<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Bank details are optional when enabling Buy Direct: the post-ad form flags
 * the seller and offers the bank details popup (saves over AJAX), but the ad
 * and the buyer are never blocked on them.
 */
class BuyDirectBankGateTest extends TestCase
{
    use DatabaseTransactions;

    private function seller(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'phone' => '09' . random_int(10000000, 99999999),
            'account_number' => null,
            'bank_code' => null,
        ], $attrs));
    }

    private function asUser(User $user)
    {
        return $this->withSession(['user_id' => $user->user_id]);
    }

    public function test_post_ad_flags_seller_without_bank_and_includes_popup(): void
    {
        $response = $this->asUser($this->seller())->get('/user/post-ad');

        $response->assertOk()
            ->assertSee('data-has-bank="0"', false)
            ->assertSee('id="bankDetailsModal"', false)
            ->assertSee('data-open-bank-modal', false)
            ->assertSee('add them now');
    }

    public function test_buy_direct_validation_does_not_require_bank_details(): void
    {
        $request = new \Illuminate\Http\Request(['buy_direct' => 'Yes', 'shipment' => 'Ship']);

        $this->assertNull(app(\App\Services\AdvertValidationService::class)->validateShipping($request, $this->seller()));
    }

    public function test_post_ad_lets_seller_with_bank_enable_buy_direct(): void
    {
        $seller = $this->seller(['account_number' => '1000123456789', 'bank_code' => '044']);

        $this->asUser($seller)->get('/user/post-ad')
            ->assertOk()
            ->assertSee('data-has-bank="1"', false)
            ->assertDontSee('data-bank-notice', false);
    }

    public function test_bank_details_save_over_ajax_returns_json(): void
    {
        $seller = $this->seller();

        $this->asUser($seller)->postJson('/user/payments', [
            'bank_name' => 'Commercial Bank of Ethiopia',
            'bank_code' => 'CBE',
            'account_name' => 'Abebe Kebede',
            'account_number' => '1000123456789',
        ])->assertOk()->assertJsonStructure(['message']);

        $seller->refresh();
        $this->assertSame('1000123456789', $seller->account_number);
        $this->assertSame('CBE', $seller->bank_code);
    }

    public function test_bank_details_ajax_validation_errors_are_json(): void
    {
        $this->asUser($this->seller())->postJson('/user/payments', ['bank_name' => 'X'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['bank_code', 'account_name', 'account_number']);
    }
}
