<?php

namespace App\Http\Controllers\Api;

use App\Services\BuyDirectEscrow;
use App\Services\FlutterwaveGateway;
use App\Http\Controllers\Controller;
use App\Mail\UnmatchedPaymentMail;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\Payment;
use App\Models\UnmatchedPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('verif-hash');
        $expected  = config('services.flutterwave.webhookHash');

        if (!$expected || !hash_equals($expected, $signature ?? '')) {
            Log::warning('Flutterwave webhook: invalid signature', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();
        $event   = $payload['event'] ?? '';

        if ($event === 'transfer.completed') {
            $this->handleTransfer($payload['data'] ?? []);
            return response()->json(['message' => 'OK'], 200);
        }

        if ($event !== 'charge.completed') {
            return response()->json(['message' => 'Event ignored'], 200);
        }

        $reference = $payload['data']['tx_ref'] ?? null;

        if (!$reference) {
            Log::error('Flutterwave webhook: missing tx_ref in payload');
            return response()->json(['message' => 'Bad payload'], 400);
        }

        // Always verify directly with Flutterwave — never trust webhook body alone
        $verified = Http::withToken(config('services.flutterwave.secretKey'))
            ->get(config('services.flutterwave.paymentUrl') . "/transactions/verify_by_reference", [
                'tx_ref' => $reference,
            ])
            ->json();

        if (($verified['status'] ?? null) !== 'success' || ($verified['data']['status'] ?? '') !== 'successful') {
            Log::warning('Flutterwave webhook: verification failed for reference', ['ref' => $reference]);
            return response()->json(['message' => 'Verification failed'], 200);
        }

        $data        = $verified['data'];
        $metadata    = $data['meta'] ?? [];
        $paymentType = $metadata['payment_type'] ?? null;

        // If payment_type is missing from metadata, infer from the reference —
        // web boost flows historically omitted this field.
        if (!$paymentType) {
            if (AdvertBoost::where('payment_reference', $reference)->exists()) {
                $paymentType = 'boost';
            } elseif (Payment::where('payment_reference', $reference)->exists()) {
                $paymentType = 'buy_direct';
            } else {
                // Flutterwave confirms money changed hands, but nothing in our DB references
                // it (e.g. a client paid directly against Flutterwave without ever calling our
                // initialize endpoint, so no AdvertBoost/Payment row was ever created).
                // Don't guess — alert an admin so the customer can be manually credited.
                $this->reportUnmatchedPayment($reference, $data);
                return response()->json(['message' => 'OK'], 200);
            }

            Log::info('Flutterwave webhook: inferred payment_type from reference lookup', [
                'ref'  => $reference,
                'type' => $paymentType,
            ]);
        }

        if ($paymentType === 'boost') {
            $this->activateBoost($reference, (int) $data['id']);
        } elseif ($paymentType === 'buy_direct') {
            // Covers buyers who close the browser/app before the redirect callback runs.
            $payment = Payment::where('payment_reference', $reference)->first();
            if ($payment) {
                BuyDirectEscrow::make()->markPaid($payment, $data);
            } else {
                $this->reportUnmatchedPayment($reference, $data);
            }
        }

        // Always return 200 — Flutterwave retries on non-200
        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Seller payout finished (or failed). Re-verified with Flutterwave before
     * the order is updated — the webhook body alone is never trusted.
     */
    private function handleTransfer(array $data): void
    {
        $reference = $data['reference'] ?? null;
        $payment = $reference ? Payment::where('payout_reference', $reference)->first() : null;

        if (!$payment || empty($data['id'])) {
            Log::info('Flutterwave webhook: transfer not linked to a Buy Direct payout', ['ref' => $reference]);
            return;
        }

        if (!$payment->payout_transfer_id) {
            $payment->update(['payout_transfer_id' => (string) $data['id']]);
        }

        $gateway = app(FlutterwaveGateway::class);
        BuyDirectEscrow::make()->syncTransfer($payment, $gateway->getTransfer((string) $data['id']));
    }

    private function reportUnmatchedPayment(string $reference, array $data): void
    {
        Log::critical('Flutterwave webhook: paid transaction has no matching AdvertBoost or Payment record', [
            'ref'    => $reference,
            'amount' => $data['amount'] ?? null,
            'email'  => $data['customer']['email'] ?? null,
        ]);

        $unmatched = UnmatchedPayment::firstOrCreate(
            ['reference' => $reference],
            [
                'transaction_id' => $data['id'] ?? null,
                'amount'         => $data['amount'] ?? 0,
                'customer_email' => $data['customer']['email'] ?? 'unknown',
                'paid_at'        => $data['created_at'] ?? null,
                'status'         => 'unresolved',
            ]
        );

        if (!$unmatched->wasRecentlyCreated) {
            // Already recorded (and already alerted) on a previous delivery of this event.
            return;
        }

        Mail::to(config('global.admin_email'))->queue(new UnmatchedPaymentMail([
            'reference'       => $reference,
            'transaction_id'  => $data['id'] ?? null,
            'amount'          => $data['amount'] ?? 0,
            'customer_email'  => $data['customer']['email'] ?? 'unknown',
            'paid_at'         => $data['created_at'] ?? 'unknown',
        ]));
    }

    private function activateBoost(string $reference, int $transactionId): void
    {
        $boost = AdvertBoost::where('payment_reference', $reference)
            ->where('payment_status', 'pending')
            ->first();

        if (!$boost) {
            // Already processed or record not found — log and move on
            Log::info('Flutterwave webhook: boost already processed or not found', ['ref' => $reference]);
            return;
        }

        DB::transaction(function () use ($boost, $transactionId) {
            $boost->update([
                'payment_status' => 'paid',
                'boost_status'   => 'active',
                'trans_id'       => $transactionId,
                'start_date'     => Carbon::now(),
            ]);

            Advert::where('id', $boost->advert_id)->update(['featured' => 'Yes']);
        });

        Log::info('Flutterwave webhook: boost activated', [
            'boost_id'  => $boost->id,
            'advert_id' => $boost->advert_id,
            'trans_id'  => $transactionId,
        ]);
    }
}
