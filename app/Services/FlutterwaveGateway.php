<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper over the Flutterwave v3 endpoints the Buy Direct escrow uses
 * (refunds, transfers). Every method returns a normalised array and never
 * throws, so callers can record the failure on the order and let an admin
 * retry.
 *
 * Shape: ['ok' => bool, 'status' => ?string, 'id' => ?string, 'message' => ?string, 'data' => array]
 */
class FlutterwaveGateway
{
    protected function client()
    {
        return Http::withToken((string) config('services.flutterwave.secretKey'))
            ->acceptJson()
            ->timeout(30);
    }

    protected function url(string $path): string
    {
        return rtrim(config('services.flutterwave.paymentUrl'), '/') . $path;
    }

    protected function normalise(?Response $response, string $context): array
    {
        if (!$response) {
            return ['ok' => false, 'status' => null, 'id' => null, 'message' => 'No response from Flutterwave', 'data' => []];
        }

        $body = $response->json() ?? [];
        $ok   = $response->successful() && ($body['status'] ?? null) === 'success';
        $data = is_array($body['data'] ?? null) ? $body['data'] : [];

        if (!$ok) {
            Log::warning("Flutterwave {$context} failed", ['http' => $response->status(), 'body' => $body]);
        }

        return [
            'ok'      => $ok,
            'status'  => isset($data['status']) ? strtolower((string) $data['status']) : null,
            'id'      => isset($data['id']) ? (string) $data['id'] : null,
            'message' => $body['message'] ?? ($ok ? null : 'Unknown Flutterwave error'),
            'data'    => $data,
        ];
    }

    protected function call(string $context, callable $fn): array
    {
        try {
            return $this->normalise($fn(), $context);
        } catch (\Throwable $e) {
            Log::error("Flutterwave {$context} exception: " . $e->getMessage());

            return ['ok' => false, 'status' => null, 'id' => null, 'message' => $e->getMessage(), 'data' => []];
        }
    }

    /** POST /transactions/{id}/refund */
    public function refund(string $transactionId, float $amount): array
    {
        return $this->call('refund', fn () => $this->client()
            ->post($this->url("/transactions/{$transactionId}/refund"), ['amount' => $amount]));
    }

    /** GET /refunds/{id} */
    public function getRefund(string $refundId): array
    {
        return $this->call('refund lookup', fn () => $this->client()->get($this->url("/refunds/{$refundId}")));
    }

    /** POST /transfers */
    public function transfer(string $bankCode, string $accountNumber, float $amount, string $reference, string $narration): array
    {
        return $this->call('transfer', fn () => $this->client()->post($this->url('/transfers'), [
            'account_bank'   => $bankCode,
            'account_number' => $accountNumber,
            'amount'         => $amount,
            'currency'       => 'ETB',
            'reference'      => $reference,
            'narration'      => $narration,
            'debit_currency' => 'ETB',
        ]));
    }

    /** GET /transfers/{id} */
    public function getTransfer(string $transferId): array
    {
        return $this->call('transfer lookup', fn () => $this->client()->get($this->url("/transfers/{$transferId}")));
    }

    /** GET /transactions/verify_by_reference */
    public function verifyByReference(string $txRef): array
    {
        return $this->call('verify', fn () => $this->client()
            ->get($this->url('/transactions/verify_by_reference'), ['tx_ref' => $txRef]));
    }
}
