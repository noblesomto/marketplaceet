<?php

namespace Tests\Feature\Api;

use App\Models\Admin;
use App\Models\Advert;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Users show up in other people's responses (an ad's seller, a message's
 * sender). Secrets never leave the server, and account details are only
 * for the user themselves or an admin.
 */
class UserPrivacyTest extends TestCase
{
    use DatabaseTransactions;

    private const SECRETS = ['password', 'remember_token', 'otp', 'otp_expires_at', 'token', 'last_login_ip', 'google_id', 'facebook_id', 'apple_id'];

    private Advert $advert;
    private User $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $advert = Advert::where('ad_status', 'active')->where('sold', 'No')->whereHas('owner')->first();
        if (!$advert) {
            $this->markTestSkipped('Needs an active advert with an owner.');
        }

        $this->advert = $advert;
        $this->seller = $advert->owner;
        $this->seller->forceFill([
            'email' => 'seller-' . uniqid() . '@example.test', 'phone' => '08011112222', 'address' => '1 Seller Street',
            'otp' => '123456', 'token' => 'secret-token', 'last_login_ip' => '10.0.0.1',
            'bank_name' => 'Test Bank', 'account_number' => '0123456789', 'disable_reason' => 'n/a',
        ])->save();
    }

    private function assertPublicProfile(array $user): void
    {
        foreach (array_merge(self::SECRETS, User::PRIVATE_FIELDS) as $field) {
            $this->assertArrayNotHasKey($field, $user, "{$field} is exposed");
        }
        $this->assertSame($this->seller->name, $user['name']);
        $this->assertSame('08011112222', $user['phone']);
    }

    public function test_ad_details_show_only_the_sellers_public_profile(): void
    {
        $data = $this->getJson("/api/adverts/{$this->advert->id}")->assertOk()->json('data');

        $this->assertPublicProfile($data['ad_owner']);
        $this->assertPublicProfile($data['ad']['owner']);
    }

    public function test_another_signed_in_user_also_gets_the_public_profile(): void
    {
        Sanctum::actingAs(User::where('id', '!=', $this->seller->id)->firstOrFail(), ['*']);

        $data = $this->getJson("/api/adverts/{$this->advert->id}")->assertOk()->json('data');

        $this->assertPublicProfile($data['ad_owner']);
    }

    public function test_users_see_their_own_account_details_but_never_secrets(): void
    {
        Sanctum::actingAs($this->seller, ['*']);

        $me = $this->getJson('/api/user')->assertOk()->json();

        $this->assertSame($this->seller->email, $me['email']);
        $this->assertSame('0123456789', $me['account_number']);
        $this->assertSame('1 Seller Street', $me['address']);
        foreach (self::SECRETS as $field) {
            $this->assertArrayNotHasKey($field, $me);
        }
    }

    public function test_admins_see_account_details(): void
    {
        $admin = Admin::first() ?? $this->markTestSkipped('Needs an admin.');
        $this->actingAs($admin, 'admin');

        $user = $this->seller->fresh()->toArray();

        $this->assertSame($this->seller->email, $user['email']);
        $this->assertSame('n/a', $user['disable_reason']);
        $this->assertArrayNotHasKey('otp', $user);
    }
}
