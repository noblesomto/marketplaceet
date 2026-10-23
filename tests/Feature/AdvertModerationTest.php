<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Advert;
use App\Models\AdvertModerationLog;
use App\Models\Notification;
use App\Mail\AdvertBannedMail;
use App\Mail\AdvertApprovedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Covers the advert ban/resubmit/review workflow: admin bans an advert with
 * a reason, the seller is notified and can resubmit after fixing it, and
 * admin reviews the resubmission before it goes live again. Also covers the
 * security fix closing the self-reactivation loopholes on both the web and
 * API ad-status routes.
 */
class AdvertModerationTest extends TestCase
{
    protected Admin $admin;
    protected array $createdUserIds = [];
    protected array $createdAdvertIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::find(1);
        if (!$this->admin) {
            $this->markTestSkipped('No seeded admin (id=1) found.');
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdAdvertIds)) {
            Advert::whereIn('id', $this->createdAdvertIds)->delete();
        }
        if (!empty($this->createdUserIds)) {
            User::whereIn('id', $this->createdUserIds)->delete();
        }

        parent::tearDown();
    }

    protected function actingAsAdmin()
    {
        return $this->actingAs($this->admin, 'admin')
            ->withSession(['admin_2fa_verified' => true]);
    }

    protected function makeAdvert(array $overrides = []): Advert
    {
        $user = User::factory()->create(['phone' => '09' . random_int(10000000, 99999999)]);
        $this->createdUserIds[] = $user->id;

        $advert = Advert::create(array_merge([
            'user_id'      => $user->user_id,
            'ad_title'     => 'Test Advert ' . Str::random(8),
            'title_slug'   => Str::slug('test-advert-' . Str::random(8)),
            'ad_type'      => 'Sell',
            'category'     => 1,
            'sub_category' => 1,
            'brand'        => 1,
            'buy_direct'   => 'No',
            'description'  => 'A test advert description.',
            'state'        => 'Addis Ababa',
            'lga'          => 'Bole',
            'state_slug'   => 'addis-ababa',
            'ad_id'        => (string) random_int(100000, 999999),
            'views'        => '0',
            'ad_status'    => 'active',
            'source'       => 'web',
        ], $overrides));

        $this->createdAdvertIds[] = $advert->id;

        return $advert;
    }

    /** @test */
    public function admin_can_ban_an_advert_with_a_reason_and_the_seller_is_notified()
    {
        Mail::fake();
        $advert = $this->makeAdvert();

        $response = $this->actingAsAdmin()->post("/admin/advert-ban/{$advert->id}", [
            'reason_category' => 'misleading',
            'reason_note' => 'Price does not match description.',
        ]);

        $response->assertRedirect();
        $advert->refresh();
        $this->assertSame('banned', $advert->ad_status);

        $this->assertDatabaseHas('advert_moderation_logs', [
            'advert_id' => $advert->id,
            'admin_id' => $this->admin->id,
            'action' => 'banned',
            'reason_category' => 'misleading',
        ]);

        $this->assertDatabaseHas('notifications', [
            'advert_id' => $advert->id,
            'type' => 'Advert Disabled',
        ]);

        Mail::assertQueued(AdvertBannedMail::class, function ($mail) use ($advert) {
            return $mail->details['ad_title'] === $advert->ad_title
                && $mail->details['is_resubmission_reject'] === false;
        });
    }

    /** @test */
    public function banning_without_a_reason_category_fails_validation()
    {
        $advert = $this->makeAdvert();

        $response = $this->actingAsAdmin()->post("/admin/advert-ban/{$advert->id}", []);

        $response->assertSessionHasErrors('reason_category');
        $advert->refresh();
        $this->assertSame('active', $advert->ad_status);
    }

    /** @test */
    public function seller_can_resubmit_a_banned_advert_for_review()
    {
        $advert = $this->makeAdvert(['ad_status' => 'banned']);
        $user = User::where('user_id', $advert->user_id)->first();

        $response = $this->withSession(['user_id' => $user->user_id])
            ->get("/user/resubmit-ad/{$advert->id}");

        $response->assertRedirect('user/my-ads');
        $advert->refresh();
        $this->assertSame('pending_review', $advert->ad_status);
        $this->assertNotNull($advert->resubmitted_at);

        $this->assertDatabaseHas('advert_moderation_logs', [
            'advert_id' => $advert->id,
            'admin_id' => null,
            'action' => 'resubmitted',
        ]);
    }

    /** @test */
    public function seller_cannot_resubmit_someone_elses_advert()
    {
        $advert = $this->makeAdvert(['ad_status' => 'banned']);
        $otherUser = User::factory()->create(['phone' => '09' . random_int(10000000, 99999999)]);
        $this->createdUserIds[] = $otherUser->id;

        $response = $this->withSession(['user_id' => $otherUser->user_id])
            ->get("/user/resubmit-ad/{$advert->id}");

        $response->assertRedirect('user/my-ads');
        $advert->refresh();
        $this->assertSame('banned', $advert->ad_status);
    }

    /** @test */
    public function admin_can_approve_a_pending_review_advert()
    {
        Mail::fake();
        $advert = $this->makeAdvert(['ad_status' => 'pending_review', 'resubmitted_at' => now()]);

        $response = $this->actingAsAdmin()->post("/admin/advert-approve/{$advert->id}");

        $response->assertRedirect();
        $advert->refresh();
        $this->assertSame('active', $advert->ad_status);

        $this->assertDatabaseHas('advert_moderation_logs', [
            'advert_id' => $advert->id,
            'admin_id' => $this->admin->id,
            'action' => 'approved',
        ]);

        Mail::assertQueued(AdvertApprovedMail::class);
    }

    /** @test */
    public function admin_can_reject_a_resubmission_with_a_new_reason()
    {
        Mail::fake();
        $advert = $this->makeAdvert(['ad_status' => 'pending_review', 'resubmitted_at' => now()]);

        $response = $this->actingAsAdmin()->post("/admin/advert-reject/{$advert->id}", [
            'reason_category' => 'poor_images',
            'reason_note' => 'Still blurry.',
        ]);

        $response->assertRedirect();
        $advert->refresh();
        $this->assertSame('banned', $advert->ad_status);

        $this->assertDatabaseHas('advert_moderation_logs', [
            'advert_id' => $advert->id,
            'action' => 'rejected',
            'reason_category' => 'poor_images',
        ]);

        Mail::assertQueued(AdvertBannedMail::class, function ($mail) {
            return $mail->details['is_resubmission_reject'] === true;
        });
    }

    /** @test */
    public function web_ad_status_route_cannot_be_used_to_self_reactivate_a_banned_advert()
    {
        $advert = $this->makeAdvert(['ad_status' => 'banned']);
        $user = User::where('user_id', $advert->user_id)->first();

        $response = $this->withSession(['user_id' => $user->user_id])
            ->get("/user/ad-status/active/{$advert->id}");

        $response->assertRedirect('user/my-ads');
        $advert->refresh();
        $this->assertSame('banned', $advert->ad_status, 'A banned advert must not be reactivatable via the plain ad-status toggle.');
    }

    /** @test */
    public function web_ad_status_route_is_scoped_to_the_owner()
    {
        $advert = $this->makeAdvert(['ad_status' => 'active']);
        $otherUser = User::factory()->create(['phone' => '09' . random_int(10000000, 99999999)]);
        $this->createdUserIds[] = $otherUser->id;

        $response = $this->withSession(['user_id' => $otherUser->user_id])
            ->get("/user/ad-status/disabled/{$advert->id}");

        $response->assertRedirect('user/my-ads');
        $advert->refresh();
        $this->assertSame('active', $advert->ad_status, 'A user must not be able to change another user\'s advert status.');
    }

    /** @test */
    public function api_update_ad_status_rejects_banned_as_a_target_status()
    {
        $advert = $this->makeAdvert();
        $user = User::where('user_id', $advert->user_id)->first();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/user/ads/{$advert->id}/status", ['status' => 'banned']);

        $response->assertStatus(422);
    }

    /** @test */
    public function api_update_ad_status_cannot_reactivate_a_banned_advert()
    {
        $advert = $this->makeAdvert(['ad_status' => 'banned']);
        $user = User::where('user_id', $advert->user_id)->first();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/user/ads/{$advert->id}/status", ['status' => 'active']);

        $response->assertStatus(404);
        $advert->refresh();
        $this->assertSame('banned', $advert->ad_status);
    }

    /** @test */
    public function api_resubmit_endpoint_moves_a_banned_advert_to_pending_review()
    {
        $advert = $this->makeAdvert(['ad_status' => 'banned']);
        $user = User::where('user_id', $advert->user_id)->first();

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/user/ads/{$advert->id}/resubmit");

        $response->assertStatus(200)->assertJsonPath('success', true);
        $advert->refresh();
        $this->assertSame('pending_review', $advert->ad_status);
    }
}
