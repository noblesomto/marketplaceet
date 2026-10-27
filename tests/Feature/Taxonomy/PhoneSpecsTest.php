<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Advert;
use App\Models\Brands;
use App\Models\PhoneDetail;
use App\Models\SubCategory;
use App\Services\AdvertValidationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Mobile Phones specs (RAM, storage): saving, the listing page, the spec
 * filters, the forms and phones:backfill-specs.
 */
class PhoneSpecsTest extends TestCase
{
    use DatabaseTransactions;

    private SubCategory $phones;
    private Brands $brand;
    private Advert $template;

    protected function setUp(): void
    {
        parent::setUp();

        $phones = SubCategory::with('category')->find(6);
        $brand = $phones ? Brands::where('subcat_id', $phones->id)->first() : null;
        $template = Advert::where('ad_status', 'active')->where('sold', 'No')->whereNotNull('state_slug')->first();

        if (!$phones || !$brand || !$template) {
            $this->markTestSkipped('Needs the Mobile Phones subcategory (with a brand) and an active advert.');
        }

        $this->phones = $phones;
        $this->brand = $brand;
        $this->template = $template;
    }

    private function makePhone(string $title, string $description = '', array $specs = []): Advert
    {
        $advert = $this->template->replicate(['title_slug']);
        $advert->ad_id = random_int(10000000, 99999999);
        $advert->ad_title = $title;
        $advert->description = $description;
        $advert->title_slug = Str::slug($title) . '-' . uniqid();
        $advert->category = $this->phones->cat_id;
        $advert->sub_category = $this->phones->id;
        $advert->brand = $this->brand->id;
        $advert->featured = 'No';
        $advert->save();

        $phone = new PhoneDetail([
            'phone_id'  => random_int(10000, 99999),
            'cat_id'    => $this->phones->cat_id,
            'brand_id'  => $this->brand->id,
            'model'     => '',
            'color'     => 'Black',
            'device'    => 'Device',
            'condition' => 'Used - Good',
        ] + $specs);
        $phone->advert()->associate($advert);
        $phone->save();

        return $advert->load('phone');
    }

    public function test_saving_reads_blank_specs_from_the_listing_and_keeps_chosen_ones(): void
    {
        $specs = PhoneDetail::specsFrom(['phone_ram' => '', 'phone_storage' => '512GB'], 'Tecno Camon 30', '<p>8gb ram, 256gb rom</p>');

        $this->assertSame(['ram' => '8GB', 'storage' => '512GB'], $specs);
    }

    public function test_specs_not_sent_keep_the_current_ones(): void
    {
        $advert = $this->makePhone('Spec keep ' . uniqid(), '', ['ram' => '12GB', 'storage' => '256GB']);

        // Older app: no phone_ram/phone_storage in the request at all
        $specs = PhoneDetail::specsFrom(['phone_condition' => 'New - Unboxed'], 'Samsung S22 128gb', '', $advert->phone);

        $this->assertSame(['ram' => '12GB', 'storage' => '256GB'], $specs);
    }

    public function test_listing_page_shows_the_specs(): void
    {
        $advert = $this->makePhone('Clean phone for sale ' . uniqid(), '', ['ram' => '12GB', 'storage' => '512GB']);

        $this->get("/{$advert->state_slug}/{$advert->title_slug}/{$advert->ad_id}")
            ->assertOk()
            ->assertSee('RAM')
            ->assertSee('12GB')
            ->assertSee('512GB');
    }

    public function test_spec_filter_returns_only_matching_listings(): void
    {
        $match = $this->makePhone('Phone spec match ' . uniqid(), '', ['ram' => '24GB', 'storage' => '2TB']);
        $miss = $this->makePhone('Phone spec miss ' . uniqid(), '', ['ram' => '24GB', 'storage' => '64GB']);

        $this->postJson('/filter/phone-details', ['sub_category' => $this->phones->id, 'ram' => ['24GB'], 'storage' => ['2TB']])
            ->assertOk()
            ->assertSee($match->ad_title)
            ->assertDontSee($miss->ad_title);
    }

    public function test_app_search_filters_on_phone_specs(): void
    {
        $match = $this->makePhone('Phone api match ' . uniqid(), '', ['storage' => '2TB']);
        $miss = $this->makePhone('Phone api miss ' . uniqid(), '', ['storage' => '64GB']);

        $this->postJson('/api/search/filter', ['sub_category' => $this->phones->id, 'phone_storage' => '2TB', 'per_page' => 100])
            ->assertOk()
            ->assertSee($match->ad_title)
            ->assertDontSee($miss->ad_title);
    }

    public function test_app_filter_options_list_phone_and_laptop_specs(): void
    {
        $this->getJson('/api/search/filters')
            ->assertOk()
            ->assertJsonPath('data.phone_specs.ram', \App\Support\PhoneSpecs::RAM)
            ->assertJsonPath('data.phone_specs.storage', \App\Support\PhoneSpecs::STORAGE)
            ->assertJsonPath('data.computer_specs', \App\Support\ComputerSpecs::allOptions());
    }

    public function test_subcategory_page_offers_the_spec_filters(): void
    {
        $this->makePhone('Phone spec sidebar ' . uniqid(), '', ['storage' => '2TB']);

        $this->get("/category/{$this->phones->category->category_slug}/{$this->phones->sub_cat_slug}")
            ->assertOk()
            ->assertSee('name="phone_storage[]"', false)
            ->assertSee('value="2TB"', false);
    }

    public function test_post_and_edit_forms_carry_the_spec_fields(): void
    {
        $owner = \App\Models\User::where('user_id', $this->template->user_id)->first();
        if (!$owner) {
            $this->markTestSkipped('Template advert has no owner.');
        }
        $advert = $this->makePhone('Phone spec form ' . uniqid(), '', ['ram' => '24GB']);

        $this->actingAs($owner, 'web')->withSession(['user_id' => $owner->user_id])
            ->get('/user/post-ad')
            ->assertOk()
            ->assertSee('name="phone_ram"', false)
            ->assertSee('name="phone_storage"', false);

        $this->actingAs($owner, 'web')->withSession(['user_id' => $owner->user_id])
            ->get("/user/edit-ad/{$advert->id}")
            ->assertOk()
            ->assertSee('<option value="24GB" selected>24GB</option>', false);
    }

    public function test_web_rules_accept_only_listed_spec_values(): void
    {
        $rules = (new AdvertValidationService())->getRules($this->phones->cat_id, $this->phones->id, false, false, 'Private', $this->brand->id);

        $this->assertFalse(Validator::make(['phone_ram' => '8GB', 'phone_storage' => ''], ['phone_ram' => $rules['phone_ram'], 'phone_storage' => $rules['phone_storage']])->fails());
        $this->assertTrue(Validator::make(['phone_storage' => '100GB'], ['phone_storage' => $rules['phone_storage']])->fails());
    }

    public function test_backfill_fills_only_empty_specs(): void
    {
        $advert = $this->makePhone('Infinix Hot 40 Pro 8gb ram 256gb', '', ['ram' => '12GB']);

        $this->artisan('phones:backfill-specs')->assertSuccessful();

        $this->assertSame(['ram' => '12GB', 'storage' => '256GB'], $advert->phone->fresh()->only(['ram', 'storage']));
    }
}
