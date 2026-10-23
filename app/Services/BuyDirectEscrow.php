<?php

namespace App\Services;

use App\Models\Advert;
use App\Models\Payment;
use App\Models\User;
use App\Support\ActivityLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Buy Direct escrow state machine.
 *
 *   pending ─(Flutterwave confirms)→ paid ─(seller ships)→ shipped ─(buyer confirms / auto-release)→ released → payout
 *                                      │                      │
 *                                      └─(cancel / missed ship deadline / dispute→refund)→ canceled → refund
 *
 * Every transition re-reads the row under a lock, so web, API, webhook and
 * the scheduler can all call in without double-paying or double-refunding.
 * Each public method returns [bool $ok, string $message].
 */
class BuyDirectEscrow
{
    public function __construct(protected FlutterwaveGateway $gateway)
    {
    }

    public static function make(): self
    {
        return app(self::class);
    }

    /* ------------------------------------------------------------------
     | Payment received
     * ----------------------------------------------------------------- */

    /**
     * Mark a pending order paid after Flutterwave has verified the charge.
     * Idempotent — returns false when the order was already paid.
     *
     * @param array $tx Verified transaction data from Flutterwave (`data` of verify_by_reference).
     */
    public function markPaid(Payment $payment, array $tx): bool
    {
        $expected = round((float) $payment->amount_paid);
        $charged  = (float) ($tx['amount'] ?? 0);
        $currency = strtoupper((string) ($tx['currency'] ?? 'ETB'));

        if ($currency !== 'ETB' || $charged + 0.5 < $expected) {
            Log::critical('Buy Direct: charged amount does not cover the order', [
                'payment_id' => $payment->id, 'expected' => $expected, 'charged' => $charged, 'currency' => $currency,
            ]);
            BuyDirectNotifier::admin(
                "Buy Direct payment amount mismatch – order {$payment->order_code}",
                ['Flutterwave transaction ' . ($tx['id'] ?? '?') . " charged {$currency} {$charged}, but the order total is Br {$expected}.", 'The order was NOT marked paid. Please investigate and refund or complete it manually.']
            );

            return false;
        }

        $marked = DB::transaction(function () use ($payment, $tx, $charged) {
            $row = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (!$row || $row->payment_status === 'paid') {
                return false;
            }

            $now = Carbon::now();
            $row->update([
                'payment_status' => 'paid',
                'trans_id'       => (string) ($tx['id'] ?? $row->trans_id),
                'amount_paid'    => $charged,
                'ship_code'      => $row->ship_code ?: Str::upper(Str::random(10)),
                'paid_at'        => $now,
                'ship_by'        => $now->copy()->addDays(BuyDirectSettings::shipDeadlineDays()),
            ]);

            Advert::whereKey($row->advert_id)->update(['sold' => 'Yes', 'sold_date' => $now]);

            $payment->setRawAttributes($row->getAttributes(), true);

            return true;
        });

        if ($marked) {
            $this->log('Buy Direct order paid', $payment);
            BuyDirectNotifier::orderPaid($payment);
        }

        return $marked;
    }

    /* ------------------------------------------------------------------
     | Seller ships
     * ----------------------------------------------------------------- */

    public function markShipped(Payment $payment, string $sellerUserId): array
    {
        $result = DB::transaction(function () use ($payment, $sellerUserId) {
            $row = Payment::with('advert')->whereKey($payment->id)->lockForUpdate()->first();

            if (!$row || $row->payment_status !== 'paid' || !$row->advert || $row->advert->user_id !== $sellerUserId) {
                return [false, 'Order not found.'];
            }
            if ($this->isCanceled($row)) {
                return [false, 'This order has been canceled.'];
            }
            if ($row->seller_status === 'shipped') {
                return [false, 'This order is already marked as shipped.'];
            }

            $now = Carbon::now();
            $row->update([
                'seller_status'      => 'shipped',
                'seller_status_date' => $now,
                'shipped_at'         => $now,
                'auto_release_at'    => $now->copy()->addDays(BuyDirectSettings::autoReleaseDays()),
            ]);

            $payment->setRawAttributes($row->getAttributes(), true);

            return [true, 'Order marked as shipped. The buyer has been notified.'];
        });

        if ($result[0]) {
            $this->log('Buy Direct order shipped', $payment);
            BuyDirectNotifier::shipped($payment);
        }

        return $result;
    }

    /* ------------------------------------------------------------------
     | Buyer confirms receipt / auto-release
     * ----------------------------------------------------------------- */

    public function confirmReceipt(Payment $payment, string $buyerUserId): array
    {
        return $this->release($payment, 'buyer', $buyerUserId);
    }

    /**
     * Release held funds to the seller.
     *
     * @param string $type buyer | auto | admin
     */
    public function release(Payment $payment, string $type, ?string $buyerUserId = null): array
    {
        $result = DB::transaction(function () use ($payment, $type, $buyerUserId) {
            $row = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (!$row || $row->payment_status !== 'paid' || ($buyerUserId && $row->user_id !== $buyerUserId)) {
                return [false, 'Order not found.'];
            }
            if ($this->isCanceled($row)) {
                return [false, 'This order was canceled and cannot be confirmed.'];
            }
            if ($row->buyer_status === 'delivered') {
                return [false, 'This order has already been confirmed.'];
            }
            if ($type !== 'admin' && $row->dispute_status === 'open') {
                return [false, 'This order has an open dispute. Our support team will resolve it.'];
            }
            if ($type === 'auto' && ($row->seller_status !== 'shipped' || !$row->auto_release_at || $row->auto_release_at->isFuture())) {
                return [false, 'Not yet due for automatic release.'];
            }

            $now = Carbon::now();
            $row->update([
                'buyer_status'       => 'delivered',
                'buyer_confirmed_at' => $now,
                'release_type'       => $type,
                // A buyer may confirm a hand-delivered item before the seller marked it shipped.
                'seller_status'      => 'shipped',
                'seller_status_date' => $row->seller_status_date ?? $now,
                'shipped_at'         => $row->shipped_at ?? $now,
            ]);

            $payment->setRawAttributes($row->getAttributes(), true);

            return [true, 'Thanks for confirming! Payment has been released to the seller.'];
        });

        if ($result[0]) {
            $this->log("Buy Direct funds released ({$type})", $payment);
            BuyDirectNotifier::released($payment, $type);

            if (BuyDirectSettings::autoPayout()) {
                $this->payout($payment);
            }
        }

        return $result;
    }

    /* ------------------------------------------------------------------
     | Cancellation + refund
     * ----------------------------------------------------------------- */

    /**
     * @param string $by buyer | seller | system | admin
     */
    public function cancel(Payment $payment, string $by, string $reason, ?string $actorUserId = null): array
    {
        $result = DB::transaction(function () use ($payment, $by, $reason, $actorUserId) {
            $row = Payment::with('advert')->whereKey($payment->id)->lockForUpdate()->first();

            if (!$row || $row->payment_status !== 'paid') {
                return [false, 'Order not found.'];
            }
            if ($by === 'buyer' && $row->user_id !== $actorUserId) {
                return [false, 'Order not found.'];
            }
            if ($by === 'seller' && (!$row->advert || $row->advert->user_id !== $actorUserId)) {
                return [false, 'Order not found.'];
            }
            if ($this->isCanceled($row)) {
                return [false, 'This order has already been canceled.'];
            }
            if ($row->buyer_status === 'delivered' || in_array($row->payout_status, ['processing', 'paid'], true)) {
                return [false, 'This order has already been completed and can no longer be canceled.'];
            }
            // Buyers/sellers can only cancel before it ships; after that it goes through a dispute.
            if (in_array($by, ['buyer', 'seller'], true) && $row->seller_status === 'shipped') {
                return [false, 'This order has already been shipped. If there is a problem, please report it instead.'];
            }

            $now = Carbon::now();
            $row->update([
                'buyer_status'       => 'canceled',
                'seller_status'      => 'canceled',
                'seller_status_date' => $now,
                'canceled_by'        => $by,
                'cancel_reason'      => Str::limit($reason, 250, ''),
                'canceled_at'        => $now,
            ]);

            // Put the item back on sale.
            if ($row->advert) {
                $row->advert->update(['sold' => 'No', 'sold_date' => null]);
            }

            $payment->setRawAttributes($row->getAttributes(), true);

            return [true, 'Order canceled. Your refund is being processed.'];
        });

        if ($result[0]) {
            $this->log("Buy Direct order canceled by {$by}", $payment, ['reason' => $reason]);
            BuyDirectNotifier::canceled($payment, $by);

            if (BuyDirectSettings::autoRefund()) {
                $this->refund($payment);
            }
        }

        return $result;
    }

    /**
     * Refund the buyer in full through Flutterwave. Safe to retry after a failure.
     */
    public function refund(Payment $payment): array
    {
        $claimed = DB::transaction(function () use ($payment) {
            $row = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (!$row || $row->payment_status !== 'paid' || !$this->isCanceled($row)) {
                return [false, 'Only canceled orders can be refunded.'];
            }
            if (!in_array($row->refund_status, ['none', 'failed'], true)) {
                return [false, $row->refund_status === 'refunded' ? 'This order has already been refunded.' : 'A refund is already in progress.'];
            }
            if (in_array($row->payout_status, ['processing', 'paid'], true)) {
                return [false, 'The seller has already been paid for this order.'];
            }
            if (!$row->trans_id) {
                $row->update(['refund_status' => 'failed', 'refund_error' => 'No Flutterwave transaction id on this order.']);
                $payment->setRawAttributes($row->getAttributes(), true);

                return [false, 'No Flutterwave transaction id on this order.'];
            }

            $row->update([
                'refund_status' => 'processing',
                'refund_amount' => $row->amount_paid,
                'refund_reason' => $row->cancel_reason,
                'refund_error'  => null,
            ]);
            $payment->setRawAttributes($row->getAttributes(), true);

            return [true, null];
        });

        if (!$claimed[0]) {
            return $claimed;
        }

        $res = $this->gateway->refund((string) $payment->trans_id, (float) $payment->refund_amount);

        if (!$res['ok']) {
            $payment->update(['refund_status' => 'failed', 'refund_error' => $res['message']]);
            $this->log('Buy Direct refund failed', $payment, ['error' => $res['message']]);
            BuyDirectNotifier::refundFailed($payment);

            return [false, 'Refund failed: ' . $res['message']];
        }

        $payment->update(['refund_reference' => $res['id']]);

        if (in_array($res['status'], ['completed', 'successful', 'succeeded'], true)) {
            $this->finalizeRefund($payment, true);

            return [true, 'Refund completed.'];
        }

        $this->log('Buy Direct refund submitted', $payment, ['refund_id' => $res['id'], 'status' => $res['status']]);

        return [true, 'Refund submitted to Flutterwave and is being processed.'];
    }

    /** Admin fallback: refund was handled outside Flutterwave's refund API. */
    public function markRefundedManually(Payment $payment, string $note): array
    {
        $payment->refresh();

        if (!$this->isCanceled($payment) || $payment->refund_status === 'refunded') {
            return [false, 'Only canceled, unrefunded orders can be marked refunded.'];
        }

        $payment->update([
            'refund_status'    => 'processing',
            'refund_amount'    => $payment->refund_amount ?? $payment->amount_paid,
            'refund_reference' => 'manual: ' . Str::limit($note, 200, ''),
        ]);
        $this->finalizeRefund($payment, true);

        return [true, 'Order marked as refunded.'];
    }

    public function finalizeRefund(Payment $payment, bool $success, ?string $error = null): void
    {
        $payment->refresh();

        if ($payment->refund_status !== 'processing') {
            return;
        }

        if ($success) {
            $payment->update(['refund_status' => 'refunded', 'refunded_at' => Carbon::now(), 'refund_error' => null]);
            $this->log('Buy Direct refund completed', $payment);
            BuyDirectNotifier::refunded($payment);
        } else {
            $payment->update(['refund_status' => 'failed', 'refund_error' => $error]);
            $this->log('Buy Direct refund failed', $payment, ['error' => $error]);
            BuyDirectNotifier::refundFailed($payment);
        }
    }

    /* ------------------------------------------------------------------
     | Payout to the seller
     * ----------------------------------------------------------------- */

    public function canPayout(Payment $payment): bool
    {
        return $payment->payment_status === 'paid'
            && $payment->buyer_status === 'delivered'
            && !$this->isCanceled($payment)
            && $payment->dispute_status !== 'open'
            && in_array($payment->payout_status, ['none', 'failed'], true)
            && in_array($payment->refund_status, ['none'], true);
    }

    /**
     * Transfer the item price (commission is kept) to the seller's bank.
     * Safe to retry after a failure; refuses to send twice.
     */
    public function payout(Payment $payment): array
    {
        $claimed = DB::transaction(function () use ($payment) {
            $row = Payment::with('advert.owner')->whereKey($payment->id)->lockForUpdate()->first();

            if (!$row || !$this->canPayout($row)) {
                return [false, 'This order is not ready for payout (the buyer must confirm receipt first, with no open dispute).', null, false];
            }

            $seller = $row->advert?->owner;
            if (!$seller || !$seller->account_number || !$seller->bank_code) {
                $row->update(['payout_status' => 'failed', 'payout_error' => 'Seller bank details are incomplete.']);
                $payment->setRawAttributes($row->getAttributes(), true);

                return [false, 'Seller bank details are incomplete.', null, true];
            }

            $attempt = (int) $row->payout_attempts + 1;
            $row->update([
                'payout_status'    => 'processing',
                'payout_amount'    => (float) $row->amount,
                'payout_reference' => 'MKT-PAYOUT-' . $row->id . '-' . $attempt . '-' . Str::upper(Str::random(6)),
                'payout_attempts'  => $attempt,
                'payout_error'     => null,
            ]);
            $payment->setRawAttributes($row->getAttributes(), true);

            return [true, null, $seller, false];
        });

        if (!$claimed[0]) {
            if ($claimed[3]) {
                BuyDirectNotifier::payoutFailed($payment);
            }

            return [false, $claimed[1]];
        }

        /** @var User $seller */
        $seller = $claimed[2];
        $res = $this->gateway->transfer(
            (string) $seller->bank_code,
            (string) $seller->account_number,
            (float) $payment->payout_amount,
            (string) $payment->payout_reference,
            'Marketplace payout – order ' . ($payment->order_code ?? $payment->id)
        );

        if (!$res['ok']) {
            $payment->update(['payout_status' => 'failed', 'payout_error' => $res['message']]);
            $this->log('Buy Direct payout failed', $payment, ['error' => $res['message']]);
            BuyDirectNotifier::payoutFailed($payment);

            return [false, 'Transfer failed: ' . $res['message']];
        }

        $payment->update(['payout_transfer_id' => $res['id']]);

        if ($res['status'] === 'successful') {
            $this->finalizePayout($payment, true);

            return [true, 'Payout completed. The seller has been notified.'];
        }

        if ($res['status'] === 'failed') {
            $this->finalizePayout($payment, false, $res['data']['complete_message'] ?? 'Transfer failed');

            return [false, 'Transfer failed: ' . ($res['data']['complete_message'] ?? 'unknown reason')];
        }

        $this->log('Buy Direct payout submitted', $payment, ['transfer_id' => $res['id'], 'status' => $res['status']]);

        return [true, 'Payout submitted to Flutterwave. It will be marked paid once the transfer completes.'];
    }

    /** Admin fallback: seller was paid outside the Flutterwave transfer API. */
    public function markPaidOutManually(Payment $payment, string $note): array
    {
        $payment->refresh();

        if (!$this->canPayout($payment)) {
            return [false, 'This order is not ready for payout.'];
        }

        $payment->update([
            'payout_status'    => 'processing',
            'payout_amount'    => (float) $payment->amount,
            'payout_reference' => 'MANUAL-' . $payment->id . '-' . Str::upper(Str::random(6)),
            'payout_error'     => Str::limit($note, 250, '') ?: null,
        ]);
        $this->finalizePayout($payment, true);

        return [true, 'Payout marked as completed. The seller has been notified.'];
    }

    public function finalizePayout(Payment $payment, bool $success, ?string $error = null): void
    {
        $payment->refresh();

        if ($payment->payout_status !== 'processing') {
            return;
        }

        if ($success) {
            $now = Carbon::now();
            $payment->update([
                'payout_status'     => 'paid',
                'paid_out_at'       => $now,
                'seller_settlement' => 'yes',
                'settlement_date'   => $now,
            ]);
            $this->log('Buy Direct payout completed', $payment);
            BuyDirectNotifier::paidOut($payment);
        } else {
            $payment->update(['payout_status' => 'failed', 'payout_error' => $error]);
            $this->log('Buy Direct payout failed', $payment, ['error' => $error]);
            BuyDirectNotifier::payoutFailed($payment);
        }
    }

    /* ------------------------------------------------------------------
     | Disputes
     * ----------------------------------------------------------------- */

    public function openDispute(Payment $payment, string $buyerUserId, string $reason): array
    {
        $result = DB::transaction(function () use ($payment, $buyerUserId, $reason) {
            $row = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (!$row || $row->payment_status !== 'paid' || $row->user_id !== $buyerUserId) {
                return [false, 'Order not found.'];
            }
            if ($this->isCanceled($row) || $row->buyer_status === 'delivered' || in_array($row->payout_status, ['processing', 'paid'], true)) {
                return [false, 'This order is already closed.'];
            }
            if ($row->dispute_status !== 'none') {
                return [false, 'A problem has already been reported for this order.'];
            }

            $row->update([
                'dispute_status'    => 'open',
                'dispute_reason'    => Str::limit(strip_tags($reason), 2000, ''),
                'dispute_opened_at' => Carbon::now(),
            ]);
            $payment->setRawAttributes($row->getAttributes(), true);

            return [true, 'Thanks — we have paused the payment to the seller and our support team will contact you shortly.'];
        });

        if ($result[0]) {
            $this->log('Buy Direct dispute opened', $payment, ['reason' => $reason]);
            BuyDirectNotifier::disputeOpened($payment);
        }

        return $result;
    }

    /**
     * @param string $resolution refund | release
     */
    public function resolveDispute(Payment $payment, string $resolution, string $note): array
    {
        $payment->refresh();

        if ($payment->dispute_status !== 'open') {
            return [false, 'This dispute is not open.'];
        }

        $payment->update([
            'dispute_status'      => 'resolved',
            'dispute_resolution'  => $resolution,
            'dispute_admin_note'  => $note,
            'dispute_resolved_at' => Carbon::now(),
        ]);
        $this->log("Buy Direct dispute resolved ({$resolution})", $payment, ['note' => $note]);
        BuyDirectNotifier::disputeResolved($payment);

        return $resolution === 'refund'
            ? $this->cancel($payment, 'admin', 'Dispute resolved in favour of the buyer: ' . $note)
            : $this->release($payment, 'admin');
    }

    /* ------------------------------------------------------------------
     | Scheduler
     * ----------------------------------------------------------------- */

    /**
     * Auto-cancel overdue unshipped orders, auto-release overdue shipped
     * orders, and poll Flutterwave for in-flight refunds/payouts.
     */
    public function processDue(): array
    {
        $stats = ['canceled' => 0, 'released' => 0, 'refunds_synced' => 0, 'payouts_synced' => 0];

        Payment::where('payment_status', 'paid')
            ->where('seller_status', 'pending')
            ->where('buyer_status', 'pending')
            ->whereNotNull('ship_by')
            ->where('ship_by', '<=', Carbon::now())
            ->where('dispute_status', '!=', 'open')
            ->orderBy('id')
            ->each(function (Payment $p) use (&$stats) {
                [$ok] = $this->cancel($p, 'system', 'The seller did not ship within ' . BuyDirectSettings::shipDeadlineDays() . ' days.');
                $stats['canceled'] += $ok ? 1 : 0;
            });

        Payment::where('payment_status', 'paid')
            ->where('seller_status', 'shipped')
            ->where('buyer_status', 'pending')
            ->where('dispute_status', '!=', 'open')
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', Carbon::now())
            ->orderBy('id')
            ->each(function (Payment $p) use (&$stats) {
                [$ok] = $this->release($p, 'auto');
                $stats['released'] += $ok ? 1 : 0;
            });

        // Released orders that weren't paid out because auto-payout was off at the time stay for admin.
        Payment::where('refund_status', 'processing')->whereNotNull('refund_reference')
            ->where('refund_reference', 'not like', 'manual:%')
            ->each(function (Payment $p) use (&$stats) {
                $res = $this->gateway->getRefund((string) $p->refund_reference);
                if (!$res['ok']) {
                    return;
                }
                if (in_array($res['status'], ['completed', 'successful', 'succeeded'], true)) {
                    $this->finalizeRefund($p, true);
                    $stats['refunds_synced']++;
                } elseif (in_array($res['status'], ['failed', 'declined'], true)) {
                    $this->finalizeRefund($p, false, 'Flutterwave refund ' . $res['status']);
                    $stats['refunds_synced']++;
                }
            });

        Payment::where('payout_status', 'processing')->whereNotNull('payout_transfer_id')
            ->each(function (Payment $p) use (&$stats) {
                $this->syncTransfer($p, $this->gateway->getTransfer((string) $p->payout_transfer_id));
                $stats['payouts_synced']++;
            });

        return $stats;
    }

    /** Apply a transfer lookup / `transfer.completed` webhook result to the order. */
    public function syncTransfer(Payment $payment, array $res): void
    {
        if (!$res['ok']) {
            return;
        }

        if ($res['status'] === 'successful') {
            $this->finalizePayout($payment, true);
        } elseif ($res['status'] === 'failed') {
            $this->finalizePayout($payment, false, $res['data']['complete_message'] ?? 'Transfer failed');
        }
    }

    /* ------------------------------------------------------------------ */

    public function isCanceled(Payment $payment): bool
    {
        return $payment->buyer_status === 'canceled' || $payment->seller_status === 'canceled';
    }

    protected function log(string $description, Payment $payment, array $extra = []): void
    {
        try {
            ActivityLog::record('buy_direct', $description, null, $payment, array_merge([
                'order_code' => $payment->order_code,
                'amount'     => $payment->amount_paid,
            ], $extra));
        } catch (\Throwable $e) {
            Log::warning('Buy Direct activity log failed: ' . $e->getMessage());
        }
    }
}
