<?php

namespace App\Services;

use App\Jobs\SendAdSoldPushNotification;
use App\Jobs\SendShippingUpdatePushNotification;
use App\Mail\BuyDirectMail;
use App\Mail\BuyDirectUpdateMail;
use App\Mail\PayoutMail;
use App\Mail\SellerMail;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Email + in-app + push notifications for each Buy Direct escrow event.
 * Every channel is best-effort: a failed notification never rolls back
 * the order state change that triggered it.
 */
class BuyDirectNotifier
{
    public static function orderPaid(Payment $payment): void
    {
        [$buyer, $seller, $title] = self::parties($payment);
        if (!$buyer || !$seller) {
            return;
        }

        $buyerName = trim($payment->first_name . ' ' . $payment->last_name) ?: $buyer->name;
        $details = [
            'advert'        => $title,
            'buyer'         => $buyerName,
            'seller'        => $seller->name,
            'phone'         => $payment->phone,
            'location'      => $payment->deliveryLocationText() ?: 'Not provided',
            'item_price'    => (float) $payment->amount,
            'commission'    => (float) $payment->commission,
            'amount_paid'   => (float) $payment->amount_paid,
            'order_code'    => $payment->order_code ?? $payment->id,
            'ship_by'       => optional($payment->ship_by)->format('d M Y, g:i A'),
            'release_days'  => BuyDirectSettings::autoReleaseDays(),
            'buyer_url'     => url('/user/order-details/' . $payment->id),
            'seller_url'    => url('/user/ad-shipping/' . $payment->advert_id),
        ];

        self::safely('paid buyer mail', fn () => Mail::to($buyer->email)->send(new BuyDirectMail($details)));
        self::safely('paid seller mail', fn () => Mail::to($seller->email)->send(new SellerMail($details)));

        self::inApp($seller, $payment, 'Item Sold', "Your advert \"{$title}\" has been purchased by {$buyerName}. Please ship it by {$details['ship_by']}.");
        self::safely('ad sold push', fn () => SendAdSoldPushNotification::dispatch($seller, $payment->advert, $buyerName));
    }

    public static function shipped(Payment $payment): void
    {
        [$buyer, , $title] = self::parties($payment);
        if (!$buyer) {
            return;
        }

        $releaseOn = optional($payment->auto_release_at)->format('d M Y');
        $body = "The seller has shipped \"{$title}\". Once you receive it, confirm receipt on your order page. If you don't confirm or report a problem by {$releaseOn}, payment is released to the seller automatically.";

        self::inApp($buyer, $payment, 'Order Shipped', $body);
        self::push($buyer, $payment, 'shipped', ['Your order has been shipped!', $body]);
        self::mail($buyer, [
            'subject'   => "Your order {$payment->order_code} has been shipped",
            'heading'   => 'Your order is on its way',
            'greeting'  => "Hello {$buyer->name},",
            'lines'     => [$body, 'If anything is wrong with the item, use "Report a problem" on your order page before that date and we will hold the payment.'],
            'cta_url'   => url('/user/order-details/' . $payment->id),
            'cta_label' => 'View order',
        ]);
    }

    public static function released(Payment $payment, string $type): void
    {
        [$buyer, $seller, $title] = self::parties($payment);

        $how = match ($type) {
            'buyer' => 'The buyer confirmed receipt of',
            'auto'  => 'The confirmation window has ended for',
            default => 'Our support team has released the payment for',
        };

        if ($seller) {
            $body = "{$how} \"{$title}\". Your payout of Br " . number_format((float) $payment->amount, 2) . ' is being processed to your bank account.';
            self::inApp($seller, $payment, 'Payment Released', $body);
            self::push($seller, $payment, 'released', ['Payment released', $body]);
        }

        if ($buyer && $type === 'auto') {
            self::inApp($buyer, $payment, 'Order Completed', "Your order for \"{$title}\" has been completed and the payment released to the seller.");
        }
    }

    public static function canceled(Payment $payment, string $by): void
    {
        [$buyer, $seller, $title] = self::parties($payment);
        $refundNote = 'A full refund of Br ' . number_format((float) $payment->amount_paid, 2) . ' is being processed to your original payment method.';

        if ($by === 'buyer' && $seller) {
            self::inApp($seller, $payment, 'Order Canceled', "The buyer canceled their order for \"{$title}\". No shipping is required and your advert is back on sale.");
            self::push($seller, $payment, 'buyer_canceled');
        }

        if (in_array($by, ['seller', 'system', 'admin'], true) && $buyer) {
            $why = match ($by) {
                'seller' => 'The seller canceled your order',
                'system' => 'The seller did not ship your order in time, so it has been canceled',
                default  => 'Your order has been canceled by our support team',
            };
            $body = "{$why} for \"{$title}\". {$refundNote}";
            self::inApp($buyer, $payment, 'Order Canceled', $body);
            self::push($buyer, $payment, 'seller_canceled', ['Your order has been canceled', $body]);
            self::mail($buyer, [
                'subject'  => "Order {$payment->order_code} canceled – refund on the way",
                'heading'  => 'Your order has been canceled',
                'greeting' => "Hello {$buyer->name},",
                'lines'    => [$body],
                'cta_url'  => url('/user/order-details/' . $payment->id),
                'tone'     => 'amber',
            ]);
        }

        if ($by === 'system' && $seller) {
            self::inApp($seller, $payment, 'Order Canceled', "Your sale of \"{$title}\" was canceled because it was not shipped within " . BuyDirectSettings::shipDeadlineDays() . ' days. The buyer has been refunded.');
        }

        if (!BuyDirectSettings::autoRefund()) {
            self::admin("Refund needed – order {$payment->order_code}", [
                "Order {$payment->order_code} (\"{$title}\") was canceled by the {$by}. Automatic refunds are switched off.",
                'Please refund Br ' . number_format((float) $payment->amount_paid, 2) . ' to the buyer from the Buy Direct refunds page.',
            ], url('/admin/buy-direct/refunds'));
        }
    }

    public static function refunded(Payment $payment): void
    {
        [$buyer, , $title] = self::parties($payment);
        if (!$buyer) {
            return;
        }

        $body = 'Your refund of Br ' . number_format((float) ($payment->refund_amount ?? $payment->amount_paid), 2) . " for \"{$title}\" has been processed. Depending on your bank it can take a few business days to appear.";
        self::inApp($buyer, $payment, 'Refund Processed', $body);
        self::push($buyer, $payment, 'refunded', ['Refund processed', $body]);
        self::mail($buyer, [
            'subject'  => "Refund processed – order {$payment->order_code}",
            'heading'  => 'Your refund has been processed',
            'greeting' => "Hello {$buyer->name},",
            'lines'    => [$body],
        ]);
    }

    public static function refundFailed(Payment $payment): void
    {
        self::admin("Refund FAILED – order {$payment->order_code}", [
            "The automatic refund for order {$payment->order_code} failed: " . ($payment->refund_error ?: 'unknown error') . '.',
            'Please retry it or refund the buyer manually from the Buy Direct refunds page.',
        ], url('/admin/buy-direct/refunds'), 'red');
    }

    public static function paidOut(Payment $payment): void
    {
        [, $seller, $title] = self::parties($payment);
        if (!$seller) {
            return;
        }

        self::safely('payout mail', fn () => Mail::to($seller->email)->send(new PayoutMail([
            'seller' => $seller->name,
            'title'  => $title,
            'amount' => $payment->payout_amount ?? $payment->amount,
            'date'   => $payment->paid_out_at,
        ])));
        self::inApp($seller, $payment, 'Payout Sent', 'Br ' . number_format((float) ($payment->payout_amount ?? $payment->amount), 2) . " for \"{$title}\" has been sent to your bank account.");
    }

    public static function payoutFailed(Payment $payment): void
    {
        self::admin("Payout FAILED – order {$payment->order_code}", [
            "The payout to the seller for order {$payment->order_code} failed: " . ($payment->payout_error ?: 'unknown error') . '.',
            'Please fix the seller\'s bank details if needed, then retry or mark it paid manually from Pending Settlements.',
        ], url('/admin/pending-settlements'), 'red');
    }

    public static function disputeOpened(Payment $payment): void
    {
        [$buyer, $seller, $title] = self::parties($payment);

        if ($seller) {
            self::inApp($seller, $payment, 'Problem Reported', "The buyer reported a problem with \"{$title}\". The payment is on hold while our support team reviews it.");
        }

        self::admin("Dispute opened – order {$payment->order_code}", [
            'Buyer: ' . ($buyer->name ?? '—') . ' (' . ($payment->phone ?? '—') . ')',
            "Item: {$title}",
            'Reason: ' . $payment->dispute_reason,
        ], url('/admin/buy-direct/disputes'), 'amber');
    }

    public static function disputeResolved(Payment $payment): void
    {
        [$buyer, $seller, $title] = self::parties($payment);
        $outcome = $payment->dispute_resolution === 'refund'
            ? 'resolved in favour of the buyer — the buyer will be refunded'
            : 'resolved in favour of the seller — the payment will be released';
        $body = "The reported problem with \"{$title}\" has been {$outcome}." . ($payment->dispute_admin_note ? ' Note: ' . $payment->dispute_admin_note : '');

        foreach (array_filter([$buyer, $seller]) as $user) {
            self::inApp($user, $payment, 'Dispute Resolved', $body);
        }
        if ($buyer) {
            self::mail($buyer, ['subject' => "Update on order {$payment->order_code}", 'heading' => 'Your report has been resolved', 'greeting' => "Hello {$buyer->name},", 'lines' => [$body]]);
        }
        if ($seller) {
            self::mail($seller, ['subject' => "Update on order {$payment->order_code}", 'heading' => 'A reported problem has been resolved', 'greeting' => "Hello {$seller->name},", 'lines' => [$body]]);
        }
    }

    /* ------------------------------------------------------------------ */

    public static function admin(string $subject, array $lines, ?string $url = null, string $tone = 'blue'): void
    {
        self::safely('admin mail', fn () => Mail::to(config('global.admin_email'))->queue(new BuyDirectUpdateMail([
            'subject'   => $subject,
            'heading'   => $subject,
            'greeting'  => 'Hello Admin,',
            'lines'     => $lines,
            'cta_url'   => $url,
            'cta_label' => 'Open admin',
            'tone'      => $tone,
        ])));
    }

    /** @return array{0:?User,1:?User,2:string} */
    protected static function parties(Payment $payment): array
    {
        $payment->loadMissing(['advert', 'user']);
        $seller = $payment->advert ? User::where('user_id', $payment->advert->user_id)->first() : null;

        return [$payment->user, $seller, $payment->advert->ad_title ?? 'your order'];
    }

    protected static function inApp(User $user, Payment $payment, string $type, string $message): void
    {
        self::safely('in-app notification', fn () => Notification::create([
            'user_id'   => $user->id,
            'seller_id' => $user->id,
            'advert_id' => $payment->advert_id,
            'type'      => $type,
            'message'   => $message,
            'is_read'   => 0,
        ]));
    }

    protected static function push(User $user, Payment $payment, string $status, ?array $message = null): void
    {
        self::safely('push notification', fn () => SendShippingUpdatePushNotification::dispatch($user, $payment, $status, $message));
    }

    protected static function mail(User $user, array $details): void
    {
        if (!$user->email) {
            return;
        }
        self::safely('update mail', fn () => Mail::to($user->email)->queue(new BuyDirectUpdateMail($details)));
    }

    protected static function safely(string $what, callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::error("Buy Direct {$what} failed: " . $e->getMessage());
        }
    }
}
