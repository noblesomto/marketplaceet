<?php

namespace App\Support;

use App\Models\Payment;

/**
 * Single source of truth for how a Buy Direct (escrow) order is presented to
 * the buyer and the seller — badge, banner copy and which actions are allowed.
 * Used by the web order pages and the mobile API so they never drift apart.
 *
 * `class` is Tailwind for web views; `color` is a semantic name for API clients.
 */
class BuyDirectStatus
{
    protected const STYLES = [
        'green'  => ['badge' => 'bg-green-100 text-green-700 border-green-200',   'banner' => 'bg-green-50 border border-green-200 text-green-800'],
        'blue'   => ['badge' => 'bg-blue-100 text-blue-700 border-blue-200',      'banner' => 'bg-blue-50 border border-blue-200 text-blue-800'],
        'amber'  => ['badge' => 'bg-amber-100 text-amber-700 border-amber-200',   'banner' => 'bg-amber-50 border border-amber-200 text-amber-800'],
        'orange' => ['badge' => 'bg-orange-100 text-orange-700 border-orange-200', 'banner' => 'bg-orange-50 border border-orange-200 text-orange-800'],
        'red'    => ['badge' => 'bg-red-100 text-red-700 border-red-200',         'banner' => 'bg-red-50 border border-red-200 text-red-800'],
    ];

    /**
     * @param string $viewer buyer | seller
     * @return array{stage:string,label:string,icon:string,color:string,class:string,banner:?array,actions:array,deadlines:array}
     */
    public static function for(Payment $p, string $viewer): array
    {
        $isBuyer   = $viewer === 'buyer';
        $canceled  = $p->buyer_status === 'canceled' || $p->seller_status === 'canceled';
        $confirmed = $p->buyer_status === 'delivered';
        $shipped   = $p->seller_status === 'shipped';
        $disputed  = $p->dispute_status === 'open';
        $paid      = $p->payment_status === 'paid';
        $fmt       = fn ($d) => $d ? $d->format('d M Y') : null;

        if (!$paid) {
            [$stage, $label, $icon, $color, $text] = ['unpaid', 'Awaiting Payment', 'bi-clock', 'amber', 'Payment for this order has not been completed yet.'];
        } elseif ($canceled) {
            $refund = match ($p->refund_status) {
                'refunded' => 'A full refund of Br ' . number_format((float) ($p->refund_amount ?? $p->amount_paid), 2) . ' was processed' . ($p->refunded_at ? ' on ' . $fmt($p->refunded_at) : '') . '.',
                default    => 'A full refund to the buyer is being processed.',
            };
            $why = match ($p->canceled_by) {
                'buyer'  => 'The buyer canceled this order.',
                'seller' => 'The seller canceled this order.',
                'system' => 'This order was canceled because it was not shipped in time.',
                'admin'  => 'This order was canceled by our support team.',
                default  => 'This order has been canceled.',
            };
            [$stage, $label, $icon, $color, $text] = ['canceled', $p->refund_status === 'refunded' ? 'Refunded' : 'Canceled', 'bi-x-circle-fill', 'red', $why . ' ' . ($isBuyer ? str_replace('to the buyer ', '', $refund) : 'The buyer is being refunded and your advert is back on sale.')];
        } elseif ($disputed) {
            [$stage, $label, $icon, $color, $text] = ['disputed', 'Problem Reported', 'bi-exclamation-triangle-fill', 'orange', $isBuyer
                ? 'You reported a problem with this order. The payment is on hold and our support team will contact you.'
                : 'The buyer reported a problem with this order. The payment is on hold while our support team reviews it.'];
        } elseif ($confirmed) {
            $paidOut = $p->payout_status === 'paid';
            $how     = match ($p->release_type) {
                'auto'  => 'The confirmation window ended and the payment was released to the seller.',
                'admin' => 'Our support team released the payment to the seller.',
                default => $isBuyer ? 'You confirmed receipt and the payment was released to the seller.' : 'The buyer confirmed receipt.',
            };
            $sellerNote = $paidOut
                ? 'Br ' . number_format((float) ($p->payout_amount ?? $p->amount), 2) . ' was sent to your bank account' . ($p->paid_out_at ? ' on ' . $fmt($p->paid_out_at) : '') . '.'
                : 'Your payout of Br ' . number_format((float) $p->amount, 2) . ' is being processed.';
            [$stage, $label, $icon, $color, $text] = ['completed', $isBuyer ? 'Completed' : ($paidOut ? 'Paid Out' : 'Payment Released'), 'bi-check-circle-fill', 'green', $isBuyer ? $how . ' Thank you for shopping with us!' : $how . ' ' . $sellerNote];
        } elseif ($shipped) {
            $release = $fmt($p->auto_release_at);
            [$stage, $label, $icon, $color, $text] = ['shipped', 'Shipped', 'bi-truck', 'blue', $isBuyer
                ? 'The seller has shipped your order' . ($p->shipped_at ? ' on ' . $fmt($p->shipped_at) : '') . '. Once you receive it, confirm below.' . ($release ? " If you don't confirm or report a problem by {$release}, the payment is released to the seller automatically." : '')
                : 'You marked this order as shipped. The payment is released when the buyer confirms receipt' . ($release ? ", or automatically on {$release}" : '') . '.'];
        } else {
            $by = $fmt($p->ship_by);
            [$stage, $label, $icon, $color, $text] = ['awaiting_shipment', $isBuyer ? 'Awaiting Shipment' : 'Ready to Ship', 'bi-box-seam', 'amber', $isBuyer
                ? 'Payment received and held securely. The seller will contact you to arrange delivery' . ($by ? " and must ship by {$by}, or you'll get a full refund." : '.')
                : 'The buyer has paid and the payment is held securely. Contact the buyer to arrange delivery' . ($by ? " and mark the order as shipped by {$by}, otherwise it is canceled automatically." : '.')];
        }

        $open = $paid && !$canceled && !$confirmed;

        return [
            'stage'  => $stage,
            'label'  => $label,
            'icon'   => $icon,
            'color'  => $color,
            'class'  => self::STYLES[$color]['badge'],
            'banner' => ['icon' => $icon, 'color' => $color, 'class' => self::STYLES[$color]['banner'], 'text' => $text],
            'actions' => $isBuyer ? [
                'can_confirm_receipt' => $open && !$disputed,
                'can_cancel'          => $open && !$shipped && !$disputed,
                'can_report_problem'  => $open && !$disputed && $p->dispute_status === 'none',
            ] : [
                'can_mark_shipped' => $open && !$shipped && !$disputed,
                'can_cancel'       => $open && !$shipped && !$disputed,
            ],
            'deadlines' => [
                'ship_by'         => optional($p->ship_by)->toIso8601String(),
                'auto_release_at' => optional($p->auto_release_at)->toIso8601String(),
            ],
        ];
    }

    /** Escrow fields exposed to API clients alongside the status. */
    public static function escrowFields(Payment $p): array
    {
        return [
            'item_price'         => (float) $p->amount,
            'commission'         => (float) $p->commission,
            'amount_paid'        => (float) $p->amount_paid,
            'paid_at'            => optional($p->paid_at)->toIso8601String(),
            'ship_by'            => optional($p->ship_by)->toIso8601String(),
            'shipped_at'         => optional($p->shipped_at)->toIso8601String(),
            'auto_release_at'    => optional($p->auto_release_at)->toIso8601String(),
            'buyer_confirmed_at' => optional($p->buyer_confirmed_at)->toIso8601String(),
            'release_type'       => $p->release_type,
            'canceled_by'        => $p->canceled_by,
            'canceled_at'        => optional($p->canceled_at)->toIso8601String(),
            'dispute_status'     => $p->dispute_status,
            'dispute_reason'     => $p->dispute_reason,
            'refund_status'      => $p->refund_status,
            'refund_amount'      => $p->refund_amount !== null ? (float) $p->refund_amount : null,
            'refunded_at'        => optional($p->refunded_at)->toIso8601String(),
            'payout_status'      => $p->payout_status,
            'paid_out_at'        => optional($p->paid_out_at)->toIso8601String(),
        ];
    }

    /** Delivery details captured at checkout. */
    public static function deliveryFields(Payment $p): array
    {
        $p->loadMissing('stateLocation');

        return [
            'name'    => trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')),
            'phone'   => $p->phone,
            'address' => $p->delivery_address,
            'zone'    => $p->delivery_lga,
            'region'  => $p->stateLocation->name ?? null,
            'full'    => $p->deliveryLocationText() ?: null,
        ];
    }
}
