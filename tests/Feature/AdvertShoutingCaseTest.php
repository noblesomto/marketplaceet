<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Advert;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\State;
use App\Models\Lga;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

/**
 * ContentHelper::sanitizeContent() and sanitizeDescription() are different
 * functions — only the latter (and sanitizeTitle()) calls
 * normalizeShoutingCase(). Three call sites were using the plain
 * sanitizeContent() and so never got ALL-CAPS listings normalized despite
 * the feature existing: the API's description field on create/update, and
 * the admin edit form's title and description.
 */
class AdvertShoutingCaseTest extends TestCase
{
    protected $user;
    protected $category;
    protected $subCategory;
    protected $brand;
    protected $state;
    protected $lga;
    protected array $createdAdvertIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first();
        if (!$this->user) {
            $this->markTestSkipped('No users found in database.');
        }
        if (empty($this->user->phone)) {
            $this->user->phone = '08012345670';
            $this->user->save();
        }

        $this->category = Category::find(3) ?? Category::first(); // Jobs — skips image requirement
        $this->subCategory = SubCategory::where('cat_id', $this->category->id)->first() ?? SubCategory::first();
        $this->brand = Brands::where('subcat_id', $this->subCategory->id)->first() ?? Brands::first();
        $this->state = State::first();
        $this->lga = Lga::where('state_id', $this->state->id)->first() ?? Lga::first();

        if (!$this->category || !$this->subCategory || !$this->state || !$this->lga) {
            $this->markTestSkipped('Required taxonomy data not found.');
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdAdvertIds)) {
            Advert::whereIn('id', $this->createdAdvertIds)->delete();
        }

        parent::tearDown();
    }

    protected function shoutingDescription(): string
    {
        return 'A MUST GO TODAY SUPER CLEAN LEXUS FULL OPTION VERY CLEAN IN AND OUT VERY SOUND ENGINE AND GEAR ONLY TODAY DEAL FOR A LUCKY CUSTOMER!';
    }

    protected function assertNotShouting(string $text, string $context)
    {
        $letters = preg_replace('/[^\p{L}]/u', '', $text);
        $upper = preg_replace('/[^\p{Lu}]/u', '', $text);
        $ratio = mb_strlen($letters) > 0 ? mb_strlen($upper) / mb_strlen($letters) : 0;

        $this->assertLessThanOrEqual(
            0.7,
            $ratio,
            "$context should not still be shouting-case: \"$text\""
        );
    }

    /** @test */
    public function api_create_advert_normalizes_shouting_description()
    {
        Sanctum::actingAs($this->user, ['*']);

        $response = $this->postJson('/api/adverts', [
            'ad_title' => 'Test Advert ' . Str::random(6),
            'ad_type' => 'Sell',
            'category' => $this->category->id,
            'subcategory' => $this->subCategory->id,
            'brand' => $this->brand->id ?? null,
            'salary' => 150000,
            'buy_direct' => 'No',
            'state' => $this->state->name,
            'lga' => $this->lga->name,
            'description' => $this->shoutingDescription(),
            'shipment' => 'No',
            'show_contact' => 'Yes',
        ]);

        $response->assertStatus(201);
        $advertId = $response->json('data.id');
        $this->createdAdvertIds[] = $advertId;

        $stored = Advert::find($advertId)->description;
        $this->assertNotShouting($stored, 'API-created advert description');
    }

    /** @test */
    public function api_update_advert_normalizes_shouting_description()
    {
        Sanctum::actingAs($this->user, ['*']);

        $advert = Advert::create([
            'user_id' => $this->user->user_id,
            'ad_title' => 'Original Title',
            'title_slug' => Str::slug('original-title-' . Str::random(6)),
            'ad_type' => 'Sell',
            'category' => $this->category->id,
            'sub_category' => $this->subCategory->id,
            'brand' => $this->brand->id ?? null,
            'salary' => 100000,
            'buy_direct' => 'No',
            'description' => 'Original lowercase description.',
            'state' => $this->state->name,
            'lga' => $this->lga->name,
            'views' => '0',
            'ad_status' => 'active',
            'source' => 'api',
        ]);
        $this->createdAdvertIds[] = $advert->id;

        $response = $this->putJson("/api/adverts/{$advert->id}", [
            'ad_title' => $advert->ad_title,
            'category' => $this->category->id,
            'subcategory' => $this->subCategory->id,
            'brand' => $this->brand->id ?? null,
            'salary' => 100000,
            'buy_direct' => 'No',
            'state' => $this->state->name,
            'lga' => $this->lga->name,
            'description' => $this->shoutingDescription(),
            'show_contact' => 'Yes',
        ]);

        $response->assertStatus(200);

        $stored = $advert->fresh()->description;
        $this->assertNotShouting($stored, 'API-updated advert description');
    }

    /** @test */
    public function admin_edit_advert_normalizes_shouting_title_and_description()
    {
        $admin = Admin::find(1);
        if (!$admin) {
            $this->markTestSkipped('No seeded admin (id=1) found.');
        }

        $advert = Advert::create([
            'user_id' => $this->user->user_id,
            'ad_title' => 'Original Title',
            'title_slug' => Str::slug('original-title-' . Str::random(6)),
            'ad_type' => 'Sell',
            'category' => $this->category->id,
            'sub_category' => $this->subCategory->id,
            'brand' => $this->brand->id ?? null,
            'salary' => 100000,
            'buy_direct' => 'No',
            'description' => 'Original lowercase description.',
            'state' => $this->state->name,
            'lga' => $this->lga->name,
            'views' => '0',
            'ad_status' => 'active',
            'source' => 'web',
        ]);
        $this->createdAdvertIds[] = $advert->id;

        $response = $this->actingAs($admin, 'admin')
            ->withSession(['admin_2fa_verified' => true])
            ->post("/admin/edit-ad/{$advert->id}", [
                'ad_title' => 'A MUST GO TODAY SUPER CLEAN DEAL',
                'ad_type' => 'Sell',
                'category' => $this->category->id,
                'subcategory' => $this->subCategory->id,
                'brand' => $this->brand->id ?? null,
                'salary' => 100000,
                'buy_direct' => 'No',
                'state' => $this->state->name,
                'lga' => $this->lga->name,
                'description' => $this->shoutingDescription(),
                'quantity' => 1,
                'show_contact' => 'Yes',
            ]);

        $fresh = $advert->fresh();
        // Guard against a false-negative: if the update silently failed
        // validation, the title/description would still be the original
        // (non-shouting) values and the assertions below would pass for
        // the wrong reason.
        $this->assertNotSame('Original Title', $fresh->ad_title, 'Admin edit did not actually apply — response: ' . $response->status());
        $this->assertNotShouting($fresh->ad_title, 'Admin-edited advert title');
        $this->assertNotShouting($fresh->description, 'Admin-edited advert description');
    }
}
