<?php

namespace Tests\Feature;

use App\Models\AdSetting;
use App\Models\Advert;
use App\Models\Lga;
use App\Models\Payment;
use App\Models\User;
use App\Services\BuyDirectEscrow;
use App\Services\BuyDirectSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Buy Direct escrow: buyer pays item + commission, platform holds it, seller
 * ships, buyer confirms (or the window expires), seller is paid out.
 * Covers deadlines, refunds, disputes and the access-control fixes.
 */
class BuyDirectEscrowTest extends TestCase
{
    protected array $userIds = [];
    protected array $advertIds = [];
    protected array $settingsBackup = [];

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Queue::fake();

        foreach (array_keys(BuyDirectSettings::DEFAULTS) as $key) {
            $this->settingsBackup[$key] = AdSetting::where('key', $key)->value('value');
        }
        BuyDirectSettings::set(BuyDirectSettings::DEFAULTS);

        Http::fake([
            '*/transfers/*'                       => Http::response(['status' => 'success', 'data' => ['id' => 555, 'status' => 'SUCCESSFUL']]),
            '*/transfers'                         => Http::response(['status' => 'success', 'data' => ['id' => 555, 'status' => 'NEW']]),
            '*/transactions/*/refund'             => Http::response(['status' => 'success', 'data' => ['id' => 777, 'status' => 'completed']]),
            '*/transactions/verify_by_reference*' => Http::response(['status' => 'success', 'data' => ['id' => 999, 'status' => 'successful', 'amount' => 0, 'currency' => 'ETB']]),
            '*/payments'                          => Http::response(['status' => 'success', 'data' => ['link' => 'https://checkout.flutterwave.com/v3/hosted/pay/test']]),
        ]);
    }

    protected function tearDown(): void
    {
        Payment::whereIn('advert_id', $this->advertIds)->delete();
        Advert::whereIn('id', $this->advertIds)->delete();
        User::whereIn('id', $this->userIds)->delete();

        foreach ($this->settingsBackup as $key => $value) {
            $value === null ? AdSetting::where('key', $key)->delete() : AdSetting::setValue($key, $value);
            \Illuminate\Support\Facades\Cache::forget("ad_setting_{$key}");
        }

        parent::tearDown();
    }

    protected function makeUser(array $attrs = []): User
    {
        $user = User::factory()->create(array_merge(['phone' => '09' . random_int(10000000, 99999999)], $attrs));
        $this->userIds[] = $user->id;

        return $user;
    }

    protected function makeSeller(): User
    {
        return $this->makeUser(['account_number' => '1000123456789', 'bank_code' => '044']);
    }

    protected function makeAdvert(User $seller, float $price = 10000): Advert
    {
        $advert = Advert::create([
            'user_id'      => $seller->user_id,
            'ad_title'     => 'Escrow Test ' . Str::random(8),
            'title_slug'   => 'escrow-test-' . Str::lower(Str::random(8)),
            'ad_type'      => 'Sell',
            'category'     => 1,
            'sub_category' => 1,
            'brand'        => 1,
            'buy_direct'   => 'Yes',
            'price'        => $price,
            'description'  => 'Escrow test advert.',
            'state'        => 'Addis Ababa',
            'lga'          => 'Bole',
            'state_slug'   => 'addis-ababa',
            'ad_id'        => (string) random_int(100000, 999999),
            'views'        => '0',
            'ad_status'    => 'active',
            'source'       => 'web',
            'sold'         => 'No',
        ]);
        $this->advertIds[] = $advert->id;

        return $advert;
    }

    /** A paid order (buyer, seller, advert, payment). */
    protected function paidOrder(float $price = 10000): array
    {
        $seller = $this->makeSeller();
        $buyer  = $this->makeUser();
        $advert = $this->makeAdvert($seller, $price);
        $quote  = BuyDirectSettings::quote($price);

        $payment = Payment::create([
            'advert_id'         => $advert->id,
            'order_code'        => strtoupper(Str::random(8)),
            'user_id'           => $buyer->user_id,
            'payment_reference' => 'TEST-' . Str::random(12),
            'first_name'        => 'Abebe',
            'last_name'         => 'Kebede',
            'phone'             => $buyer->phone,
            'amount'            => $quote['item_price'],
            'commission'        => $quote['commission'],
            'amount_paid'       => $quote['grand_total'],
            'payment_status'    => 'pending',
            'source'            => 'web',
        ]);

        $this->assertTrue(BuyDirectEscrow::make()->markPaid($payment, ['id' => 4242, 'amount' => $quote['grand_total'], 'currency' => 'ETB']));

        return [$buyer, $seller, $advert, $payment->fresh()];
    }

    /** @test */
    public function commission_follows_admin_settings_and_threshold()
    {
        BuyDirectSettings::set(['buy_direct_commission_rate' => 5, 'buy_direct_commission_rate_high' => 1, 'buy_direct_commission_threshold' => 50000]);

        $this->assertSame(500.0, BuyDirectSettings::quote(10000)['commission']);
        $this->assertSame(10500.0, BuyDirectSettings::quote(10000)['grand_total']);
        $this->assertSame(600.0, BuyDirectSettings::quote(60000)['commission']);
    }

    /** @test */
    public function marking_paid_holds_funds_sets_ship_deadline_and_is_idempotent()
    {
        BuyDirectSettings::set(['buy_direct_ship_deadline_days' => 3]);
        [, , $advert, $payment] = $this->paidOrder();

        $this->assertSame('paid', $payment->payment_status);
        $this->assertSame('none', $payment->payout_status);
        $this->assertTrue($payment->ship_by->between(now()->addDays(3)->subMinute(), now()->addDays(3)->addMinute()));
        $this->assertSame('Yes', $advert->fresh()->sold);

        $this->assertFalse(BuyDirectEscrow::make()->markPaid($payment, ['id' => 4242, 'amount' => $payment->amount_paid, 'currency' => 'ETB']));
    }

    /** @test */
    public function an_underpaid_charge_is_not_marked_paid()
    {
        $seller = $this->makeSeller();
        $buyer  = $this->makeUser();
        $advert = $this->makeAdvert($seller);
        $payment = Payment::create([
            'advert_id' => $advert->id, 'user_id' => $buyer->user_id, 'payment_reference' => 'TEST-' . Str::random(12),
            'phone' => $buyer->phone, 'amount' => 10000, 'commission' => 300, 'amount_paid' => 10300,
            'payment_status' => 'pending', 'source' => 'web',
        ]);

        $this->assertFalse(BuyDirectEscrow::make()->markPaid($payment, ['id' => 1, 'amount' => 100, 'currency' => 'ETB']));
        $this->assertSame('pending', $payment->fresh()->payment_status);
    }

    /** @test */
    public function only_the_seller_can_mark_shipped_and_it_starts_the_release_window()
    {
        BuyDirectSettings::set(['buy_direct_auto_release_days' => 7]);
        [$buyer, $seller, , $payment] = $this->paidOrder();

        [$ok] = BuyDirectEscrow::make()->markShipped($payment, $buyer->user_id);
        $this->assertFalse($ok);

        [$ok] = BuyDirectEscrow::make()->markShipped($payment, $seller->user_id);
        $this->assertTrue($ok);

        $payment->refresh();
        $this->assertSame('shipped', $payment->seller_status);
        $this->assertTrue($payment->auto_release_at->between(now()->addDays(7)->subMinute(), now()->addDays(7)->addMinute()));
    }

    /** @test */
    public function payout_is_blocked_until_the_buyer_confirms()
    {
        [, $seller, , $payment] = $this->paidOrder();
        BuyDirectEscrow::make()->markShipped($payment, $seller->user_id);

        [$ok] = BuyDirectEscrow::make()->payout($payment->fresh());

        $this->assertFalse($ok);
        $this->assertSame('none', $payment->fresh()->payout_status);
        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/transfers'));
    }

    /** @test */
    public function buyer_confirmation_releases_funds_and_pays_the_seller_the_item_price_once()
    {
        [$buyer, $seller, , $payment] = $this->paidOrder(10000);
        BuyDirectEscrow::make()->markShipped($payment, $seller->user_id);

        [$ok] = BuyDirectEscrow::make()->confirmReceipt($payment->fresh(), $buyer->user_id);
        $this->assertTrue($ok);

        $payment->refresh();
        $this->assertSame('delivered', $payment->buyer_status);
        $this->assertSame('buyer', $payment->release_type);
        $this->assertSame('processing', $payment->payout_status);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/transfers') && (float) $r['amount'] === 10000.0 && $r['account_number'] === '1000123456789');

        // Transfer completes (webhook / poll)
        BuyDirectEscrow::make()->syncTransfer($payment, ['ok' => true, 'status' => 'successful', 'data' => []]);
        $payment->refresh();
        $this->assertSame('paid', $payment->payout_status);
        $this->assertSame('yes', $payment->seller_settlement);

        // A second payout attempt is refused.
        [$again] = BuyDirectEscrow::make()->payout($payment);
        $this->assertFalse($again);
    }

    /** @test */
    public function another_user_cannot_confirm_receipt_via_the_api()
    {
        [, $seller, , $payment] = $this->paidOrder();
        BuyDirectEscrow::make()->markShipped($payment, $seller->user_id);
        $stranger = $this->makeUser();

        $this->actingAs($stranger, 'sanctum')
            ->postJson("/api/payments/{$payment->id}/mark-delivered")
            ->assertStatus(422);

        $this->assertSame('pending', $payment->fresh()->buyer_status);
    }

    /** @test */
    public function unshipped_orders_past_the_deadline_are_canceled_refunded_and_relisted()
    {
        [, , $advert, $payment] = $this->paidOrder();
        $payment->update(['ship_by' => Carbon::now()->subMinute()]);

        $stats = BuyDirectEscrow::make()->processDue();

        $payment->refresh();
        $this->assertGreaterThanOrEqual(1, $stats['canceled']);
        $this->assertSame('canceled', $payment->buyer_status);
        $this->assertSame('system', $payment->canceled_by);
        $this->assertSame('refunded', $payment->refund_status);
        $this->assertSame('No', $advert->fresh()->sold);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/transactions/4242/refund'));
    }

    /** @test */
    public function shipped_orders_past_the_window_are_released_automatically()
    {
        [, $seller, , $payment] = $this->paidOrder();
        BuyDirectEscrow::make()->markShipped($payment, $seller->user_id);
        $payment->update(['auto_release_at' => Carbon::now()->subMinute()]);

        BuyDirectEscrow::make()->processDue();

        $payment->refresh();
        $this->assertSame('delivered', $payment->buyer_status);
        $this->assertSame('auto', $payment->release_type);
        $this->assertContains($payment->payout_status, ['processing', 'paid']);
    }

    /** @test */
    public function an_open_dispute_freezes_release_until_admin_resolves_it()
    {
        [$buyer, $seller, , $payment] = $this->paidOrder();
        BuyDirectEscrow::make()->markShipped($payment, $seller->user_id);

        [$ok] = BuyDirectEscrow::make()->openDispute($payment->fresh(), $buyer->user_id, 'The item arrived broken and unusable.');
        $this->assertTrue($ok);

        $payment->update(['auto_release_at' => Carbon::now()->subMinute()]);
        BuyDirectEscrow::make()->processDue();
        $this->assertSame('pending', $payment->fresh()->buyer_status);

        [$ok] = BuyDirectEscrow::make()->resolveDispute($payment->fresh(), 'refund', 'Seller agreed to take it back.');
        $this->assertTrue($ok);

        $payment->refresh();
        $this->assertSame('resolved', $payment->dispute_status);
        $this->assertSame('admin', $payment->canceled_by);
        $this->assertSame('refunded', $payment->refund_status);
        $this->assertSame('none', $payment->payout_status);
    }

    /** @test */
    public function a_failed_refund_can_be_completed_manually_by_admin()
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['*/transactions/*/refund' => Http::response(['status' => 'error', 'message' => 'Insufficient balance'], 400)]);
        [$buyer, , , $payment] = $this->paidOrder();

        BuyDirectEscrow::make()->cancel($payment, 'buyer', 'Changed my mind', $buyer->user_id);

        $payment->refresh();
        $this->assertSame('failed', $payment->refund_status);
        $this->assertSame('Insufficient balance', $payment->refund_error);

        [$ok] = BuyDirectEscrow::make()->markRefundedManually($payment, 'Bank transfer ref 123');
        $this->assertTrue($ok);
        $this->assertSame('refunded', $payment->fresh()->refund_status);
    }

    /** @test */
    public function buyer_cannot_cancel_after_the_item_is_shipped()
    {
        [$buyer, $seller, , $payment] = $this->paidOrder();
        BuyDirectEscrow::make()->markShipped($payment, $seller->user_id);

        [$ok] = BuyDirectEscrow::make()->cancel($payment->fresh(), 'buyer', 'Too late', $buyer->user_id);

        $this->assertFalse($ok);
        $this->assertSame('shipped', $payment->fresh()->seller_status);
    }

    /** @test */
    public function api_checkout_charges_the_server_side_price_not_the_client_amount()
    {
        $lga = Lga::first();
        if (!$lga) {
            $this->markTestSkipped('No seeded zones.');
        }

        $seller = $this->makeSeller();
        $buyer  = $this->makeUser();
        $advert = $this->makeAdvert($seller, 20000);

        $this->actingAs($buyer, 'sanctum')->postJson('/api/payments/initialize', [
            'advert_id'   => $advert->id,
            'first_name'  => 'Abebe',
            'last_name'   => 'Kebede',
            'phone'       => '0911223344',
            'state'       => $lga->state_id,
            'lga'         => $lga->name,
            'address'     => 'Near Edna Mall, House 12',
            'grand_total' => 1, // ignored
        ])->assertOk()->assertJsonPath('success', true);

        $payment = Payment::where('advert_id', $advert->id)->firstOrFail();
        $quote = BuyDirectSettings::quote(20000);

        $this->assertEquals($quote['grand_total'], (float) $payment->amount_paid);
        $this->assertSame($lga->name, $payment->delivery_lga);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/payments') && (float) $r['amount'] === round($quote['grand_total']));
    }

    /** @test */
    public function buy_direct_requires_seller_bank_details()
    {
        $seller = $this->makeUser(['account_number' => null, 'bank_code' => null]);
        $buyer  = $this->makeUser();
        $advert = $this->makeAdvert($seller);

        $this->actingAs($buyer, 'sanctum')
            ->getJson("/api/adverts/{$advert->id}/buy-direct")
            ->assertStatus(422);
    }

    /** @test */
    public function web_checkout_flow_renders_and_stores_delivery_details()
    {
        $lga = Lga::first();
        if (!$lga) {
            $this->markTestSkipped('No seeded zones.');
        }

        $seller = $this->makeSeller();
        $buyer  = $this->makeUser();
        $advert = $this->makeAdvert($seller, 15000);
        $session = ['user_id' => $buyer->user_id];

        $this->withSession($session)->get("/buy-direct/{$advert->ad_id}")
            ->assertOk()->assertSee('Delivery Location')->assertDontSee('Select a shipping option');

        $this->withSession($session)->post("/calculate-shipping/{$advert->id}", [
            'first_name' => 'Abebe', 'last_name' => 'Kebede', 'phone' => '0911223344',
            'state' => $lga->state_id, 'lga' => $lga->name, 'address' => 'Near Edna Mall, House 12',
        ])->assertRedirect(route('buy.direct.payment', ['id' => $advert->id]));

        $this->get(route('buy.direct.payment', ['id' => $advert->id]))
            ->assertOk()->assertSee($lga->name)->assertSee(number_format(BuyDirectSettings::quote(15000)['grand_total'], 2));

        $this->post(route('flutterwave.pay'))->assertRedirect('https://checkout.flutterwave.com/v3/hosted/pay/test');

        $payment = Payment::where('advert_id', $advert->id)->firstOrFail();
        $this->assertSame('Near Edna Mall, House 12', $payment->delivery_address);
        $this->assertNull($payment->shipping_method);
    }

    /** @test */
    public function buyer_and_seller_order_pages_render_with_escrow_actions()
    {
        [$buyer, $seller, $advert, $payment] = $this->paidOrder();

        $this->withSession(['user_id' => $seller->user_id])->get("/user/ad-shipping/{$advert->id}")
            ->assertOk()->assertSee('Mark as Shipped');

        $this->withSession(['user_id' => $seller->user_id])
            ->post("/user/update-shipping/{$payment->id}", ['seller_status' => 'shipped'])
            ->assertRedirect();
        $this->assertSame('shipped', $payment->fresh()->seller_status);

        $this->withSession(['user_id' => $buyer->user_id])->get("/user/order-details/{$payment->id}")
            ->assertOk()->assertSee('Confirm Received')->assertSee('Report a Problem');

        $this->withSession(['user_id' => $buyer->user_id])
            ->postJson("/user/report-problem/{$payment->id}", ['reason' => 'Item has not arrived yet at all.'])
            ->assertOk();
        $this->assertSame('open', $payment->fresh()->dispute_status);
    }

    /** @test */
    public function admin_escrow_pages_render()
    {
        $admin = \App\Models\Admin::find(1);
        if (!$admin) {
            $this->markTestSkipped('No seeded admin (id=1) found.');
        }
        $this->paidOrder();

        foreach (['/admin/pending-settlements', '/admin/buy-direct/refunds', '/admin/buy-direct/disputes', '/settings/buy-direct'] as $url) {
            $this->actingAs($admin, 'admin')->withSession(['admin_2fa_verified' => true])->get($url)->assertOk();
        }

        $this->actingAs($admin, 'admin')->withSession(['admin_2fa_verified' => true])
            ->post('/settings/buy-direct', [
                'buy_direct_ship_deadline_days' => 5, 'buy_direct_auto_release_days' => 10,
                'buy_direct_commission_rate' => 4, 'buy_direct_commission_rate_high' => 2,
                'buy_direct_commission_threshold' => 300000, 'buy_direct_auto_payout' => 1, 'buy_direct_auto_refund' => 0,
            ])->assertRedirect('/settings/buy-direct');

        $this->assertSame(5, BuyDirectSettings::shipDeadlineDays());
        $this->assertSame(10, BuyDirectSettings::autoReleaseDays());
        $this->assertFalse(BuyDirectSettings::autoRefund());
    }
}
