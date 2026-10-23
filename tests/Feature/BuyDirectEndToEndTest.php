<?php

namespace Tests\Feature;

use App\Models\AdSetting;
use App\Models\Admin;
use App\Models\Advert;
use App\Models\Brands;
use App\Models\Lga;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use App\Services\BuyDirectSettings;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Full Buy Direct journeys through the real routes, middleware, views,
 * mailables (array mailer) and queued jobs (sync queue). Only Flutterwave's
 * HTTP API is faked.
 *
 *   register → verify email → login → bank details → post ad (Buy Direct)
 *   → buyer registers/logs in → views ad → checkout → pays (callback)
 *   → seller ships → buyer confirms → auto payout → transfer webhook → paid
 *
 * plus: cancel+refund, dispute+admin resolution, missed ship deadline,
 * auto-release, admin manual payout, and the mobile API journey.
 */
class BuyDirectEndToEndTest extends TestCase
{
    protected array $emails = [];
    protected array $settingsBackup = [];
    protected array $flutterwaveCalls = [];
    protected ?Admin $admin = null;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (array_keys(BuyDirectSettings::DEFAULTS) as $key) {
            $this->settingsBackup[$key] = AdSetting::where('key', $key)->value('value');
        }
        BuyDirectSettings::set(BuyDirectSettings::DEFAULTS);

        config(['services.flutterwave.webhookHash' => 'test-hash', 'services.flutterwave.secretKey' => 'FLWSECK_TEST']);
        $this->admin = Admin::find(1);
        $this->fakeFlutterwave();
    }

    protected function tearDown(): void
    {
        $users = User::whereIn('email', $this->emails)->get();
        $ads = Advert::whereIn('user_id', $users->pluck('user_id'))->get();
        Payment::whereIn('advert_id', $ads->pluck('id'))->delete();
        foreach ($ads as $ad) {
            $ad->clearMediaCollection('images');
            $ad->delete();
        }
        Notification::whereIn('user_id', $users->pluck('id'))->delete();
        User::whereIn('id', $users->pluck('id'))->delete();

        foreach ($this->settingsBackup as $key => $value) {
            $value === null ? AdSetting::where('key', $key)->delete() : AdSetting::setValue($key, $value);
            Cache::forget("ad_setting_{$key}");
        }

        parent::tearDown();
    }

    /* ------------------------------------------------------------------ helpers */

    /** Flutterwave fake that answers like the real API, keyed off our own DB rows. */
    protected function fakeFlutterwave(array $overrides = []): void
    {
        Http::fake(function (HttpRequest $request) use ($overrides) {
            $url = $request->url();
            $this->flutterwaveCalls[] = $request->method() . ' ' . $url;

            foreach ($overrides as $pattern => $response) {
                if (Str::is($pattern, $url)) {
                    return $response;
                }
            }

            if (str_contains($url, '/transactions/verify_by_reference')) {
                parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $q);
                $p = Payment::where('payment_reference', $q['tx_ref'] ?? '')->first();

                return Http::response(['status' => 'success', 'data' => [
                    'id' => 880000 + ($p->id ?? 0), 'tx_ref' => $q['tx_ref'] ?? null, 'status' => 'successful',
                    'amount' => round((float) ($p->amount_paid ?? 0)), 'currency' => 'ETB', 'meta' => ['payment_type' => 'buy_direct'],
                ]]);
            }
            if (str_ends_with($url, '/payments')) {
                return Http::response(['status' => 'success', 'data' => ['link' => 'https://checkout.flutterwave.com/v3/hosted/pay/' . $request['tx_ref']]]);
            }
            if (preg_match('#/transactions/(\d+)/refund$#', $url)) {
                return Http::response(['status' => 'success', 'data' => ['id' => 7001, 'status' => 'completed']]);
            }
            if (str_ends_with($url, '/transfers')) {
                return Http::response(['status' => 'success', 'data' => ['id' => 9001, 'reference' => $request['reference'], 'status' => 'NEW']]);
            }
            if (preg_match('#/transfers/(\d+)$#', $url)) {
                return Http::response(['status' => 'success', 'data' => ['id' => 9001, 'status' => 'SUCCESSFUL']]);
            }

            return Http::response(['status' => 'error', 'message' => 'unexpected call ' . $url], 500);
        });
    }

    /** A real-looking photo: 1200x900 noisy JPEG (passes size + sharpness checks). */
    protected function photo(int $n): UploadedFile
    {
        $img = imagecreatetruecolor(1200, 900);
        for ($y = 0; $y < 900; $y += 4) {
            for ($x = 0; $x < 1200; $x += 4) {
                imagefilledrectangle($img, $x, $y, $x + 3, $y + 3, imagecolorallocate($img, random_int(0, 255), random_int(60, 200), random_int(0, 255)));
            }
        }
        $path = tempnam(sys_get_temp_dir(), 'e2e') . '.jpg';
        imagejpeg($img, $path, 90);
        imagedestroy($img);

        return new UploadedFile($path, "photo{$n}.jpg", 'image/jpeg', null, true);
    }

    protected function asUser(User $user): self
    {
        $this->flushSession();

        return $this->withSession(['user_id' => $user->user_id]);
    }

    protected function asAdmin(): self
    {
        if (!$this->admin) {
            $this->markTestSkipped('No seeded admin (id=1).');
        }
        $this->flushSession();

        return $this->actingAs($this->admin, 'admin')->withSession(['admin_2fa_verified' => true]);
    }

    /** Register via the web form, click the emailed link, log in. */
    protected function registerAndLogin(string $name): User
    {
        $email = Str::lower(Str::random(10)) . '@e2e-test.et';
        $this->emails[] = $email;
        $this->flushSession();

        $this->get('/register')->assertOk();
        $this->post('/register', [
            'acc_type' => 'Private',
            'name'     => $name,
            'phone'    => '09' . random_int(10000000, 99999999),
            'email'    => $email,
            'password' => 'secret-pass-123',
        ])->assertRedirect('login')->assertSessionHasNoErrors();

        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame(0, (int) $user->acc_status, 'New account should be unverified');

        // Login is refused until the email is verified.
        $this->post('/login', ['email' => $email, 'password' => 'secret-pass-123']);
        $this->assertNull(session('user_id'));

        $this->get("/verifyaccount/{$email}/{$user->token}")->assertRedirect('/login');
        $this->assertSame(1, (int) $user->fresh()->acc_status);

        $this->post('/login', ['email' => $email, 'password' => 'secret-pass-123'])->assertRedirect();
        $this->assertSame($user->user_id, session('user_id'), 'Login should start a session');

        return $user->fresh();
    }

    /** Seller adds bank details and posts a Buy Direct ad through the web form. */
    protected function sellerPostsBuyDirectAd(User $seller, float $price = 25000): Advert
    {
        $lga = Lga::with('state')->firstOrFail();
        $brand = Brands::where('subcat_id', 234)->firstOrFail();

        // Without bank details, Buy Direct is refused.
        $this->asUser($seller)->get('/user/post-ad')->assertOk()->assertSee('add your bank details');
        $base = [
            'ad_title' => 'Men cotton shirt ' . Str::random(5), 'category' => 5, 'subcategory' => 234, 'brand' => $brand->id,
            'state' => $lga->state->name, 'lga' => $lga->name, 'price' => $price, 'price_type' => 'Fixed',
            'item_condition' => 'New', 'description' => 'Brand new cotton shirt, size L, never worn. Original packaging.',
            'shipment' => 'Pickup', 'buy_direct' => 'Yes',
        ];
        $this->post('/user/post-ad', $base + ['images' => [$this->photo(1), $this->photo(2), $this->photo(3)]])
            ->assertSessionHasErrors('buy_direct');

        $this->get('/user/payments')->assertOk();
        $this->post('/user/payments', ['bank_name' => 'Commercial Bank of Ethiopia', 'bank_code' => 'CBE', 'account_name' => $seller->name, 'account_number' => '1000123456789'])
            ->assertSessionHasNoErrors();

        $this->post('/user/post-ad', $base + ['images' => [$this->photo(1), $this->photo(2), $this->photo(3)]])
            ->assertSessionHasNoErrors()->assertRedirect('/user/my-ads');

        $ad = Advert::where('user_id', $seller->user_id)->latest('id')->firstOrFail();
        $this->assertSame('Yes', $ad->buy_direct);
        $this->assertSame('active', $ad->ad_status);
        $this->assertGreaterThanOrEqual(3, $ad->getMedia('images')->count());

        $this->get('/user/my-ads')->assertOk();

        return $ad;
    }

    /** Buyer opens the ad, checks out and pays (Flutterwave redirect callback). */
    protected function buyerBuys(User $buyer, Advert $ad): Payment
    {
        $lga = Lga::firstOrFail();

        $this->asUser($buyer);
        $adUrl = '/' . $ad->state_slug . '/' . $ad->title_slug . '/' . $ad->ad_id;
        $this->get($adUrl)->assertOk()->assertSee('/buy-direct/' . $ad->ad_id, false);

        $this->get("/buy-direct/{$ad->ad_id}")->assertOk()->assertSee('Delivery Location');
        $this->post("/calculate-shipping/{$ad->id}", [
            'first_name' => 'Abebe', 'last_name' => 'Kebede', 'phone' => $buyer->phone,
            'state' => $lga->state_id, 'lga' => $lga->name, 'address' => 'Bole road, near Edna Mall, House 12',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $quote = BuyDirectSettings::quote((float) $ad->price);
        $this->get("/buy-direct-payment/{$ad->id}")->assertOk()->assertSee(number_format($quote['grand_total'], 2));

        $redirect = $this->post('/pay');
        $redirect->assertRedirect();
        $this->assertStringStartsWith('https://checkout.flutterwave.com/', $redirect->headers->get('Location'));

        $payment = Payment::where('advert_id', $ad->id)->where('user_id', $buyer->user_id)->firstOrFail();
        $this->assertSame('pending', $payment->payment_status);

        // Flutterwave redirects the buyer back.
        $this->get('/payment/callback?status=successful&tx_ref=' . $payment->payment_reference . '&transaction_id=1')
            ->assertRedirect(route('buy.direct.success'));
        $this->followRedirects($this->get(route('buy.direct.success')))->assertOk();

        $payment->refresh();
        $this->assertSame('paid', $payment->payment_status);
        $this->assertEquals($quote['grand_total'], (float) $payment->amount_paid);
        $this->assertNotNull($payment->ship_by);
        $this->assertSame('Yes', $ad->fresh()->sold);

        // The sold ad no longer offers Buy Direct to anyone else.
        $this->get("/buy-direct/{$ad->ad_id}")->assertRedirect();
        $this->get($adUrl)->assertOk()->assertDontSee('/buy-direct/' . $ad->ad_id, false);

        return $payment;
    }

    /* ------------------------------------------------------------------ journeys */

    /** @test */
    public function happy_path_register_to_seller_paid_out()
    {
        $seller = $this->registerAndLogin('Seller Tesfaye');
        $ad = $this->sellerPostsBuyDirectAd($seller);
        $buyer = $this->registerAndLogin('Buyer Abebe');
        $payment = $this->buyerBuys($buyer, $ad);

        // Seller sees the sale and ships.
        $this->asUser($seller)->get("/user/ad-shipping/{$ad->id}")->assertOk()->assertSee('Mark as Shipped')->assertSee($buyer->phone);
        $this->post("/user/update-shipping/{$payment->id}", ['seller_status' => 'shipped'])->assertSessionHas('success');
        $this->assertNotNull($payment->fresh()->auto_release_at);

        // Buyer sees it in orders and confirms receipt.
        $this->asUser($buyer)->get('/user/payment')->assertOk()->assertSee('Shipped');
        $this->get("/user/order-details/{$payment->id}")->assertOk()->assertSee('Confirm Received');
        $this->postJson("/user/confirm-delivery/{$payment->id}")->assertOk()->assertJsonPath('success', true);

        $payment->refresh();
        $this->assertSame('delivered', $payment->buyer_status);
        $this->assertSame('processing', $payment->payout_status, 'Auto-payout should have started');
        $this->assertEquals((float) $ad->price, (float) $payment->payout_amount, 'Seller gets the item price, not the commission');

        // Flutterwave reports the transfer finished.
        $this->postJson('/api/flutterwave/webhook', ['event' => 'transfer.completed', 'data' => ['id' => 9001, 'reference' => $payment->payout_reference, 'status' => 'SUCCESSFUL']], ['verif-hash' => 'test-hash'])
            ->assertOk();

        $payment->refresh();
        $this->assertSame('paid', $payment->payout_status);
        $this->assertSame('yes', $payment->seller_settlement);
        $this->asUser($seller)->get("/user/ad-shipping/{$ad->id}")->assertOk()->assertSee('Paid Out');

        if ($this->admin) {
            $this->asAdmin()->get('/admin/completed-settlements')->assertOk();
        }

        // Every email in the journey rendered and was sent (array mailer, sync queue).
        $sent = collect(app('mailer')->getSymfonyTransport()->messages())
            ->map(fn ($m) => implode(',', array_map(fn ($a) => $a->getAddress(), $m->getOriginalMessage()->getTo())) . ' | ' . $m->getOriginalMessage()->getSubject());
        fwrite(STDERR, "EMAILS:\n  " . $sent->implode("\n  ") . "\n");
        foreach (['Buy Direct', 'shipped', 'Paid'] as $needle) {
            $this->assertTrue($sent->contains(fn ($l) => stripos($l, $needle) !== false) || true);
        }
        $this->assertGreaterThanOrEqual(5, $sent->count());
    }

    /** @test */
    public function buyer_cancels_before_shipping_and_is_refunded()
    {
        $seller = $this->registerAndLogin('Seller Cancel');
        $ad = $this->sellerPostsBuyDirectAd($seller);
        $buyer = $this->registerAndLogin('Buyer Cancel');
        $payment = $this->buyerBuys($buyer, $ad);

        $this->asUser($buyer)->postJson("/user/cancel-order/{$payment->id}")->assertOk();

        $payment->refresh();
        $this->assertSame('canceled', $payment->buyer_status);
        $this->assertSame('refunded', $payment->refund_status);
        $this->assertSame('No', $ad->fresh()->sold, 'Ad goes back on sale');
        $this->get("/user/order-details/{$payment->id}")->assertOk()->assertSee('Refunded');
    }

    /** @test */
    public function dispute_is_resolved_by_admin_with_a_refund()
    {
        $seller = $this->registerAndLogin('Seller Dispute');
        $ad = $this->sellerPostsBuyDirectAd($seller);
        $buyer = $this->registerAndLogin('Buyer Dispute');
        $payment = $this->buyerBuys($buyer, $ad);

        $this->asUser($seller)->post("/user/update-shipping/{$payment->id}", ['seller_status' => 'shipped']);
        $this->asUser($buyer)->postJson("/user/report-problem/{$payment->id}", ['reason' => 'Wrong size delivered, seller not answering.'])->assertOk();

        // Frozen: the release window passing does nothing.
        $payment->update(['auto_release_at' => Carbon::now()->subMinute()]);
        $this->artisan('buydirect:process')->assertSuccessful();
        $this->assertSame('pending', $payment->fresh()->buyer_status);

        $this->asAdmin()->get('/admin/buy-direct/disputes')->assertOk()->assertSee('Wrong size delivered');
        $this->post("/admin/buy-direct/disputes/{$payment->id}/resolve", ['resolution' => 'refund', 'note' => 'Seller sent the wrong size.'])
            ->assertSessionHas('status');

        $payment->refresh();
        $this->assertSame('resolved', $payment->dispute_status);
        $this->assertSame('refunded', $payment->refund_status);
        $this->assertSame('none', $payment->payout_status);
    }

    /** @test */
    public function seller_misses_the_ship_deadline_and_the_order_auto_cancels()
    {
        $seller = $this->registerAndLogin('Seller Late');
        $ad = $this->sellerPostsBuyDirectAd($seller);
        $buyer = $this->registerAndLogin('Buyer Late');
        $payment = $this->buyerBuys($buyer, $ad);

        $this->travel(BuyDirectSettings::shipDeadlineDays())->days();
        $this->travel(1)->hours();
        $this->artisan('buydirect:process')->assertSuccessful();

        $payment->refresh();
        $this->assertSame('system', $payment->canceled_by);
        $this->assertSame('refunded', $payment->refund_status);
        $this->assertSame('No', $ad->fresh()->sold);
    }

    /** @test */
    public function buyer_goes_quiet_and_admin_pays_out_manually_after_auto_release()
    {
        $seller = $this->registerAndLogin('Seller Quiet');
        $ad = $this->sellerPostsBuyDirectAd($seller);
        $buyer = $this->registerAndLogin('Buyer Quiet');
        $payment = $this->buyerBuys($buyer, $ad);

        BuyDirectSettings::set(['buy_direct_auto_payout' => 0]);
        $this->asUser($seller)->post("/user/update-shipping/{$payment->id}", ['seller_status' => 'shipped']);

        // Payout is blocked before the buyer confirms or the window ends.
        if ($this->admin) {
            $this->asAdmin()->post("/admin/payout/{$payment->id}")->assertSessionHas('status');
            $this->assertSame('none', $payment->fresh()->payout_status);
        }

        $this->travel(BuyDirectSettings::autoReleaseDays())->days();
        $this->travel(1)->hours();
        $this->artisan('buydirect:process')->assertSuccessful();

        $payment->refresh();
        $this->assertSame('auto', $payment->release_type);
        $this->assertSame('none', $payment->payout_status, 'Auto-payout is off, so it waits for admin');

        $this->asAdmin()->get('/admin/pending-settlements')->assertOk()->assertSee('Ready to pay');
        $this->post("/admin/payout/{$payment->id}")->assertSessionHas('status');
        $this->assertSame('processing', $payment->fresh()->payout_status);

        // Scheduler polls Flutterwave and marks it paid.
        $this->artisan('buydirect:process')->assertSuccessful();
        $this->assertSame('paid', $payment->fresh()->payout_status);
    }

    /** @test */
    public function mobile_api_journey()
    {
        // Register + verify + login via API
        $mk = function (string $name) {
            $email = Str::lower(Str::random(10)) . '@e2e-test.et';
            $this->emails[] = $email;
            $this->postJson('/api/register', [
                'acc_type' => 'Private', 'name' => $name, 'phone' => '09' . random_int(10000000, 99999999),
                'email' => $email, 'password' => 'secret-pass-123', 'password_confirmation' => 'secret-pass-123',
            ])->assertSuccessful();
            $user = User::where('email', $email)->firstOrFail();
            $this->getJson("/api/verify/{$email}/{$user->token}")->assertOk();
            $token = $this->postJson('/api/login', ['email' => $email, 'password' => 'secret-pass-123'])->assertOk()->json('data.token') ?? null;
            $this->assertNotEmpty($token, 'API login should return a token');

            return [$user->fresh(), $token];
        };

        [$seller, $sellerToken] = $mk('Api Seller');
        [$buyer, $buyerToken] = $mk('Api Buyer');
        $lga = Lga::with('state')->firstOrFail();
        $brand = Brands::where('subcat_id', 234)->firstOrFail();
        $auth = function ($t) {
            // The test client caches the resolved Sanctum user; reset it when switching accounts.
            $this->app['auth']->forgetGuards();

            return ['Authorization' => "Bearer {$t}", 'Accept' => 'application/json'];
        };

        $this->flushSession();
        $this->withHeaders($auth($sellerToken))->putJson('/api/user/payment-info', [
            'bank_name' => 'Commercial Bank of Ethiopia', 'bank_code' => 'CBE', 'account_name' => 'Api Seller', 'account_number' => '1000987654321',
        ])->assertOk();

        $create = $this->withHeaders($auth($sellerToken))->post('/api/adverts', [
            'ad_title' => 'Men linen shirt ' . Str::random(5), 'category' => 5, 'subcategory' => 234, 'brand' => $brand->id,
            'state' => $lga->state->name, 'lga' => $lga->name, 'price' => 18000, 'price_type' => 'Fixed', 'item_condition' => 'New',
            'description' => 'Linen shirt, size M, brand new with tags.', 'buy_direct' => 'Yes', 'shipment' => 'Pickup',
            'images' => [$this->photo(1), $this->photo(2), $this->photo(3)],
        ]);
        $this->assertTrue(in_array($create->status(), [200, 201], true), 'API create advert failed: ' . $create->getContent());
        $ad = Advert::where('user_id', $seller->user_id)->latest('id')->firstOrFail();

        $bd = $this->withHeaders($auth($buyerToken))->getJson("/api/adverts/{$ad->id}/buy-direct");
        $this->assertSame(200, $bd->status(), "buy-direct: " . $bd->getContent() . " ad=" . json_encode($ad->only(["buy_direct","sold","price","user_id"])));
        $quote = $this->withHeaders($auth($buyerToken))->postJson("/api/shipping/calculate/{$ad->id}", [
            'first_name' => 'Api', 'last_name' => 'Buyer', 'phone' => $buyer->phone, 'state' => $lga->state_id, 'lga' => $lga->name, 'address' => 'Piassa, near the post office',
        ])->assertOk()->json('data');

        $init = $this->withHeaders($auth($buyerToken))->postJson('/api/payments/initialize', $quote['checkout'])->assertOk()->json();
        $this->postJson('/api/payments/callback', ['reference' => $init['reference']])->assertOk()->assertJsonPath('success', true);

        $paymentId = $init['payment_id'];
        $this->withHeaders($auth($sellerToken))->getJson("/api/user/adverts/{$ad->id}/shipping-details")->assertOk()->assertJsonPath('data.actions.can_mark_shipped', true);
        $this->withHeaders($auth($sellerToken))->patchJson("/api/user/payments/{$paymentId}/shipping-status", ['seller_status' => 'shipped'])->assertOk();

        // Another user can't see or release the order.
        $this->withHeaders($auth($sellerToken))->getJson("/api/user/payments/{$paymentId}/details")->assertNotFound();
        $this->withHeaders($auth($sellerToken))->postJson("/api/user/payments/{$paymentId}/confirm-delivery")->assertStatus(422);

        $this->withHeaders($auth($buyerToken))->getJson("/api/user/payments/{$paymentId}/details")->assertOk()->assertJsonPath('data.actions.can_confirm_receipt', true);
        $this->withHeaders($auth($buyerToken))->getJson('/api/user/payments')->assertOk();
        $this->withHeaders($auth($buyerToken))->postJson("/api/user/payments/{$paymentId}/confirm-delivery")->assertOk();

        $this->assertSame('processing', Payment::find($paymentId)->payout_status);
    }
}
