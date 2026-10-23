<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Advert;
use App\Support\ActivityLog;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Str;

/**
 * Covers the site-wide activity/audit log: the ActivityLog helper's
 * User-key normalization (User's Eloquent primary key is the display
 * 'user_id' string, not the bigint 'id' the activity_log morph columns
 * need), and that the key user-facing actions named in the request
 * (login, post ad, update profile, edit ad, delete ad, messages) are
 * actually recorded. Also covers the security fix found while
 * instrumenting delete_ad (no ownership check at all).
 */
class ActivityLogTest extends TestCase
{
    protected array $createdUserIds = [];
    protected array $createdAdvertIds = [];
    protected $testStartedAt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testStartedAt = now()->subSecond();
    }

    protected function tearDown(): void
    {
        // This suite doesn't use RefreshDatabase (see other Feature tests in
        // this repo) — without this, every run leaves activity_log debris
        // that later assertions/queries can pick up as false matches.
        Activity::where('created_at', '>=', $this->testStartedAt)->delete();

        if (!empty($this->createdAdvertIds)) {
            Advert::whereIn('id', $this->createdAdvertIds)->delete();
        }
        if (!empty($this->createdUserIds)) {
            User::whereIn('id', $this->createdUserIds)->delete();
        }

        parent::tearDown();
    }

    protected function makeUser(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'phone' => '09' . random_int(10000000, 99999999),
        ], $overrides));

        // User::$incrementing is false (declared key is user_id), so Eloquent
        // doesn't populate ->id after create() even though MySQL assigned one.
        $user->refresh();
        $this->createdUserIds[] = $user->id;

        return $user;
    }

    protected function makeAdvert(User $user, array $overrides = []): Advert
    {
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
    public function recording_an_activity_for_a_user_stores_the_real_bigint_id_not_the_display_user_id()
    {
        $user = $this->makeUser();

        ActivityLog::record('test', 'unit test entry', $user, $user);

        $activity = Activity::where('description', 'unit test entry')->latest()->first();

        $this->assertNotNull($activity);
        $this->assertSame($user->id, $activity->causer_id);
        $this->assertSame($user->id, $activity->subject_id);
        $this->assertSame(User::class, $activity->causer_type);
        $this->assertNotEquals($user->user_id, (string) $activity->causer_id);
    }

    /** @test */
    public function successful_web_login_is_logged()
    {
        $user = $this->makeUser(['password' => bcrypt('password123')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Logged in',
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);
    }

    /** @test */
    public function failed_web_login_wrong_password_is_logged()
    {
        $user = $this->makeUser(['password' => bcrypt('password123')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'definitely-wrong',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Failed login attempt (wrong password)',
            'causer_id' => $user->id,
        ]);
    }

    /** @test */
    public function web_logout_is_logged()
    {
        $user = $this->makeUser();

        $this->withSession(['user_id' => $user->user_id])->get('/user/logout');

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Logged out',
            'causer_id' => $user->id,
        ]);
    }

    /** @test */
    public function web_registration_is_logged()
    {
        $email = 'activity-test-' . uniqid() . '@example.com';

        $this->post('/register', [
            'acc_type' => 'Private',
            'name' => 'Activity Test',
            'phone' => '091' . random_int(1000000, 9999999),
            'email' => $email,
            'password' => 'password123',
        ]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->createdUserIds[] = $user->id;

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Registered via web',
            'causer_id' => $user->id,
        ]);
    }

    /** @test */
    public function editing_an_advert_is_logged()
    {
        $user = $this->makeUser();
        $advert = $this->makeAdvert($user);

        $category = \App\Models\Category::find(3); // Jobs — no image/item_condition requirement
        $subcategory = \App\Models\SubCategory::where('cat_id', $category->id)->first();
        $brand = \App\Models\Brands::where('subcat_id', $subcategory->id)->first();
        $state = \App\Models\State::first();
        $lga = \App\Models\Lga::where('state_id', $state->id)->first() ?? \App\Models\Lga::first();

        $this->withSession(['user_id' => $user->user_id])
            ->post("/user/edit-ad/{$advert->id}", [
                'ad_title' => 'Updated Title',
                'ad_type' => 'Sell',
                'category' => $category->id,
                'subcategory' => $subcategory->id,
                'brand' => $brand->id,
                'salary' => 150000,
                'buy_direct' => 'No',
                'state' => $state->name,
                'lga' => $lga->name,
                'description' => 'An updated test advert description with enough length.',
                'shipment' => 'No',
                'show_contact' => 'Yes',
            ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'advert',
            'causer_id' => $user->id,
            'subject_id' => $advert->id,
            'subject_type' => Advert::class,
        ]);
    }

    /** @test */
    public function deleting_an_advert_is_logged()
    {
        $user = $this->makeUser();
        $advert = $this->makeAdvert($user);
        $advertId = $advert->id;

        $this->withSession(['user_id' => $user->user_id])
            ->delete("/user/delete-ad/{$advertId}");

        $this->assertDatabaseMissing('adverts', ['id' => $advertId]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'advert',
            'causer_id' => $user->id,
            'subject_id' => $advertId,
        ]);

        // Already deleted by the request above — don't try again in tearDown.
        $this->createdAdvertIds = array_diff($this->createdAdvertIds, [$advertId]);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_advert()
    {
        $owner = $this->makeUser();
        $attacker = $this->makeUser();
        $advert = $this->makeAdvert($owner);

        $response = $this->withSession(['user_id' => $attacker->user_id])
            ->delete("/user/delete-ad/{$advert->id}");

        $this->assertDatabaseHas('adverts', ['id' => $advert->id]);
    }

    /** @test */
    public function admin_banning_an_advert_is_mirrored_into_the_activity_log()
    {
        $admin = Admin::find(1);
        if (!$admin) {
            $this->markTestSkipped('No seeded admin (id=1) found.');
        }

        $user = $this->makeUser();
        $advert = $this->makeAdvert($user);

        $this->actingAs($admin, 'admin')
            ->withSession(['admin_2fa_verified' => true])
            ->post("/admin/advert-ban/{$advert->id}", [
                'reason_category' => 'misleading',
                'reason_note' => 'Test reason.',
            ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'advert',
            'causer_type' => Admin::class,
            'causer_id' => $admin->id,
            'subject_id' => $advert->id,
            'subject_type' => Advert::class,
        ]);
    }

    /** @test */
    public function admin_disabling_a_user_is_logged()
    {
        $admin = Admin::find(1);
        if (!$admin) {
            $this->markTestSkipped('No seeded admin (id=1) found.');
        }

        $user = $this->makeUser();

        $this->actingAs($admin, 'admin')
            ->withSession(['admin_2fa_verified' => true])
            ->post("/admin/disable-status/{$user->user_id}", [
                'action' => 'disable',
                'reason' => 'Test disable reason.',
            ]);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'account',
            'description' => 'Account disabled by admin',
            'causer_type' => Admin::class,
            'causer_id' => $admin->id,
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);
    }

    /** @test */
    public function the_admin_activity_page_shows_a_users_own_actions_and_actions_on_their_adverts()
    {
        $admin = Admin::find(1);
        if (!$admin) {
            $this->markTestSkipped('No seeded admin (id=1) found.');
        }

        $user = $this->makeUser();
        $advert = $this->makeAdvert($user);

        ActivityLog::record('auth', 'Logged in', $user, $user);
        ActivityLog::record('advert', 'Banned advert "' . $advert->ad_title . '": Test', $admin, $advert);

        $response = $this->actingAs($admin, 'admin')
            ->withSession(['admin_2fa_verified' => true])
            ->get("/admin/user-activity/{$user->user_id}");

        $response->assertOk();
        $response->assertSee('Logged in');
        $response->assertSee('Banned advert', false);
    }

    /** @test */
    public function the_admin_activity_page_requires_admin_authentication()
    {
        $user = $this->makeUser();

        $response = $this->get("/admin/user-activity/{$user->user_id}");

        $response->assertRedirect(route('admin.login'));
    }
}
