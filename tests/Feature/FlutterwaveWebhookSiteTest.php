<?php

namespace Tests\Feature;

use App\Models\UnmatchedPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Several countries share one Flutterwave account: events whose reference
 * carries another country's prefix are ignored, not verified or reported.
 */
class FlutterwaveWebhookSiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.flutterwave.webhookHash' => 'test-hash', 'services.flutterwave.site' => 'ET']);
        Http::fake();
        Mail::fake();
    }

    public function test_another_sites_charge_is_ignored(): void
    {
        $before = UnmatchedPayment::count();

        $this->postJson('/api/flutterwave/webhook', [
            'event' => 'charge.completed',
            'data'  => ['id' => 123, 'tx_ref' => 'KE-MKT-ABCDEFGHIJKLMNOPQRST', 'status' => 'successful'],
        ], ['verif-hash' => 'test-hash'])
            ->assertOk()
            ->assertJsonPath('message', 'Event ignored');

        Http::assertNothingSent();
        Mail::assertNothingOutgoing();
        $this->assertSame($before, UnmatchedPayment::count());
    }

    public function test_another_sites_transfer_is_ignored(): void
    {
        $this->postJson('/api/flutterwave/webhook', [
            'event' => 'transfer.completed',
            'data'  => ['id' => 9001, 'reference' => 'GH-MKT-PAYOUT-1-1-ABCDEF', 'status' => 'SUCCESSFUL'],
        ], ['verif-hash' => 'test-hash'])->assertOk();

        Http::assertNothingSent();
    }

    public function test_unprefixed_legacy_charges_are_still_handled(): void
    {
        Http::fake(['*' => Http::response(['status' => 'error'], 200)]);

        $this->postJson('/api/flutterwave/webhook', [
            'event' => 'charge.completed',
            'data'  => ['id' => 123, 'tx_ref' => 'MKT-ABCDEFGHIJKLMNOPQRST', 'status' => 'successful'],
        ], ['verif-hash' => 'test-hash'])->assertOk();

        // Verified with Flutterwave, i.e. treated as this site's charge
        Http::assertSentCount(1);
    }
}
