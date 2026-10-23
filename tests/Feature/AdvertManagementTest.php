<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Advert;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\State;
use App\Models\Lga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdvertManagementTest extends TestCase
{
    use WithFaker;

    protected $user;
    protected $category;
    protected $subCategory;
    protected $brand;
    protected $state;
    protected $lga;

    protected function setUp(): void
    {
        parent::setUp();

        // Get or create test user
        $this->user = User::first();

        if (!$this->user) {
            $this->markTestSkipped('No users found in database. Please seed the database first.');
        }

        // Ensure user has a phone number
        if (empty($this->user->phone)) {
            $this->user->phone = '0912345678';
            $this->user->save();
        }

        // Get test data — use Jobs (id=3) which skips the mandatory image requirement
        $this->category = Category::find(3) ?? Category::first();
        $this->subCategory = SubCategory::where('cat_id', $this->category->id)->first()
            ?? SubCategory::first();
        $this->brand = Brands::first();
        $this->state = State::first();
        $this->lga = Lga::where('state_id', $this->state->id ?? null)->first() ?? Lga::first();

        if (!$this->category || !$this->subCategory || !$this->brand || !$this->state || !$this->lga) {
            $this->markTestSkipped('Required test data (category, subcategory, brand, state, lga) not found. Please seed the database.');
        }
    }

    /** @test */
    public function test_user_can_view_post_ad_form()
    {
        $response = $this->actingAs($this->user, 'web')
            ->withSession(['user_id' => $this->user->user_id])
            ->get('/user/post-ad');

        $response->assertStatus(200);
        $response->assertViewIs('user.post-ad');
        $response->assertViewHas(['title', 'categories', 'user', 'shippings', 'states']);
    }

    /** @test */
    public function test_user_can_create_advert_successfully()
    {
        Storage::fake('public');

        $advertData = [
            'ad_title'      => 'Test Job Ad ' . $this->faker->uuid,
            'ad_type'       => 'Sell',
            'category'      => $this->category->id,
            'subcategory'   => $this->subCategory->id,
            'brand'         => $this->brand->id ?? null,
            'salary'        => 150000,
            'buy_direct'    => 'No',
            'state'         => $this->state->name,
            'lga'           => $this->lga->name,
            'description'   => 'This is a test job description with more than 20 characters to pass validation.',
            'shipment'      => 'No',
            'show_contact'  => 'Yes',
        ];

        $response = $this->actingAs($this->user, 'web')
            ->withSession(['user_id' => $this->user->user_id])
            ->post('/user/post-ad', $advertData);

        $response->assertRedirect('/user/my-ads');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('adverts', [
            'ad_title' => $advertData['ad_title'],
            'user_id'  => $this->user->user_id,
        ]);
    }

    /** @test */
    public function test_post_ad_validates_required_fields()
    {
        $response = $this->actingAs($this->user, 'web')
            ->withSession(['user_id' => $this->user->user_id])
            ->post('/user/post-ad', []);

        $response->assertSessionHasErrors(['ad_title', 'description']);
    }

    /** @test */
    public function test_user_without_phone_number_cannot_post_ad()
    {
        // Create or update user without phone
        $userWithoutPhone = User::factory()->create(['phone' => null]);

        $response = $this->actingAs($userWithoutPhone, 'web')
            ->withSession(['user_id' => $userWithoutPhone->user_id])
            ->get('/user/post-ad');

        $response->assertRedirect('/user/profile-update');
        $response->assertSessionHas('profile_required');
    }

    /** @test */
    public function test_user_can_view_edit_ad_form()
    {
        // Create a test advert
        $advert = Advert::where('user_id', $this->user->user_id)->first();

        if (!$advert) {
            $this->markTestSkipped('No adverts found for test user.');
        }

        $response = $this->actingAs($this->user, 'web')
            ->withSession(['user_id' => $this->user->user_id])
            ->get('/user/edit-ad/' . $advert->id);

        $response->assertStatus(200);
        $response->assertViewIs('user.edit-ad');
        $response->assertViewHas(['title', 'categories', 'user', 'advert', 'subcategories', 'brands']);
    }

    /** @test */
    public function test_user_can_update_advert()
    {
        // Get an existing advert
        $advert = Advert::where('user_id', $this->user->user_id)->first();

        if (!$advert) {
            $this->markTestSkipped('No adverts found for test user to edit.');
        }

        $updateData = [
            'ad_title'      => 'Updated Test Product ' . $this->faker->uuid,
            'ad_type'       => $advert->ad_type,
            'category'      => $advert->category,
            'subcategory'   => $advert->sub_category,
            'brand'         => $advert->brand,
            'price'         => 75000,
            'price_type'    => 'Fixed',
            'contact_price' => 'No',
            'item_condition' => $advert->item_condition ?: 'New',
            'buy_direct'    => 'No',
            'salary'        => $advert->salary,
            'expected_salary' => $advert->expected_salary,
            'state'         => $advert->state,
            'lga'           => $advert->lga,
            'description'   => 'This is an updated test product description with sufficient content.',
            'shipment'      => 'No',
            'show_contact'  => 'Yes',
        ];

        $response = $this->actingAs($this->user, 'web')
            ->withSession(['user_id' => $this->user->user_id])
            ->post('/user/edit-ad/' . $advert->id, $updateData);

        $response->assertRedirect('/user/my-ads');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('adverts', [
            'id'       => $advert->id,
            'ad_title' => $updateData['ad_title'],
        ]);
    }

    /** @test */
    public function test_user_cannot_edit_another_users_advert()
    {
        // Get an advert from a different user
        $anotherAdvert = Advert::where('user_id', '!=', $this->user->user_id)->first();

        if (!$anotherAdvert) {
            $this->markTestSkipped('No adverts from other users found.');
        }

        $response = $this->actingAs($this->user, 'web')
            ->withSession(['user_id' => $this->user->user_id])
            ->get('/user/edit-ad/' . $anotherAdvert->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function test_duplicate_advert_is_prevented()
    {
        $existingAdvert = Advert::where('user_id', $this->user->user_id)->first();

        if (!$existingAdvert) {
            $this->markTestSkipped('No existing advert found for duplicate test.');
        }

        $duplicateData = [
            'ad_title'        => $existingAdvert->ad_title,
            'ad_type'         => 'Sell',
            'category'        => $existingAdvert->category,
            'subcategory'     => $existingAdvert->sub_category,
            'brand'           => $existingAdvert->brand,
            'price'           => $existingAdvert->price,
            'price_type'      => 'Fixed',
            'contact_price'   => 'No',
            'item_condition'  => 'New',
            'buy_direct'      => 'No',
            'salary'          => $existingAdvert->salary,
            'expected_salary' => $existingAdvert->expected_salary,
            'state'           => $existingAdvert->state,
            'lga'             => $existingAdvert->lga,
            'description'     => 'Test description for duplicate check.',
            'shipment'        => 'No',
            'show_contact'    => 'Yes',
        ];

        $response = $this->actingAs($this->user, 'web')
            ->withSession(['user_id' => $this->user->user_id])
            ->post('/user/post-ad', $duplicateData);

        $response->assertRedirect('/user/my-ads');
        $response->assertSessionHas('error');
    }
}
