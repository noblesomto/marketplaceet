<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Advert;
use App\Models\Brands;
use App\Models\ComputerDetail;
use App\Models\SubCategory;
use App\Services\AdvertValidationService;
use App\Support\DeviceModels;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Laptops & Computers specs (processor, RAM, storage): saving, the listing
 * page, the spec filters and computers:backfill-specs.
 */
class ComputerSpecsTest extends TestCase
{
    use DatabaseTransactions;

    private SubCategory $computers;
    private Brands $other;
    private Advert $template;

    protected function setUp(): void
    {
        parent::setUp();

        $computers = SubCategory::with('category')->find(DeviceModels::COMPUTERS_SUBCATEGORY_ID);
        $other = $computers ? Brands::where('subcat_id', $computers->id)->where('brand', 'Other')->first() : null;
        $template = Advert::where('ad_status', 'active')->where('sold', 'No')->whereNotNull('state_slug')->first();

        if (!$computers || !$other || !$template) {
            $this->markTestSkipped('Needs the Laptops & Computers subcategory (with its Other brand) and an active advert.');
        }

        $this->computers = $computers;
        $this->other = $other;
        $this->template = $template;
    }

    private function makeAdvert(string $title, string $description = ''): Advert
    {
        $advert = $this->template->replicate(['title_slug']);
        $advert->ad_id = random_int(10000000, 99999999);
        $advert->ad_title = $title;
        $advert->description = $description;
        $advert->title_slug = Str::slug($title) . '-' . uniqid();
        $advert->category = $this->computers->cat_id;
        $advert->sub_category = $this->computers->id;
        $advert->brand = $this->other->id;
        $advert->featured = 'No';
        $advert->save();

        return $advert;
    }

    public function test_saving_reads_blank_specs_from_the_listing_and_keeps_chosen_ones(): void
    {
        $advert = $this->makeAdvert('Clean laptop Core i5', '<p>8gb ram, 256gb ssd, keyboard light</p>');

        $detail = ComputerDetail::syncFor($advert, $this->other->id, null, null, ['processor' => 'Intel Core i7', 'ram' => '']);

        $this->assertSame(
            ['processor' => 'Intel Core i7', 'ram' => '8GB', 'storage' => '256GB', 'storage_type' => 'SSD'],
            $detail->only(['processor', 'ram', 'storage', 'storage_type'])
        );
    }

    public function test_listing_page_shows_the_specs(): void
    {
        $advert = $this->makeAdvert('Clean laptop for sale ' . uniqid());
        ComputerDetail::syncFor($advert, $this->other->id, null, null, ['processor' => 'AMD Ryzen 7', 'ram' => '32GB', 'storage' => '1TB', 'storage_type' => 'SSD']);

        $this->get("/{$advert->state_slug}/{$advert->title_slug}/{$advert->ad_id}")
            ->assertOk()
            ->assertSee('AMD Ryzen 7')
            ->assertSee('32GB')
            ->assertSee('1TB SSD');
    }

    public function test_spec_filter_returns_only_matching_listings(): void
    {
        $match = $this->makeAdvert('Spec filter match ' . uniqid());
        $miss = $this->makeAdvert('Spec filter miss ' . uniqid());
        ComputerDetail::syncFor($match, $this->other->id, null, null, ['ram' => '48GB', 'processor' => 'Intel Xeon']);
        ComputerDetail::syncFor($miss, $this->other->id, null, null, ['ram' => '4GB', 'processor' => 'Intel Xeon']);

        $this->postJson('/filter/computer-details', ['sub_category' => $this->computers->id, 'ram' => ['48GB'], 'processor' => ['Intel Xeon']])
            ->assertOk()
            ->assertSee($match->ad_title)
            ->assertDontSee($miss->ad_title);
    }

    public function test_subcategory_page_offers_the_spec_filters(): void
    {
        $advert = $this->makeAdvert('Spec sidebar ' . uniqid());
        ComputerDetail::syncFor($advert, $this->other->id, null, null, ['ram' => '48GB']);

        $this->get("/category/{$this->computers->category->category_slug}/{$this->computers->sub_cat_slug}")
            ->assertOk()
            ->assertSee('name="computer_ram[]"', false)
            ->assertSee('value="48GB"', false);
    }

    public function test_post_and_edit_forms_carry_the_specs_card(): void
    {
        $owner = \App\Models\User::where('user_id', $this->template->user_id)->first();
        if (!$owner) {
            $this->markTestSkipped('Template advert has no owner.');
        }
        $advert = $this->makeAdvert('Spec form ' . uniqid());
        ComputerDetail::syncFor($advert, $this->other->id, null, null, ['ram' => '24GB']);

        $this->actingAs($owner, 'web')->withSession(['user_id' => $owner->user_id])
            ->get('/user/post-ad')
            ->assertOk()
            ->assertSee('id="divComputer"', false)
            ->assertSee('name="processor"', false);

        $this->actingAs($owner, 'web')->withSession(['user_id' => $owner->user_id])
            ->get("/user/edit-ad/{$advert->id}")
            ->assertOk()
            ->assertSee('<option value="24GB" selected>24GB</option>', false);
    }

    public function test_web_rules_accept_only_listed_spec_values(): void
    {
        $rules = (new AdvertValidationService())->getRules($this->computers->cat_id, $this->computers->id, false, false, 'Private', $this->other->id);

        $this->assertFalse(Validator::make(['ram' => '16GB', 'storage' => ''], ['ram' => $rules['ram'], 'storage' => $rules['storage']])->fails());
        $this->assertTrue(Validator::make(['ram' => '15GB'], ['ram' => $rules['ram']])->fails());
    }

    public function test_backfill_fills_only_empty_specs(): void
    {
        $advert = $this->makeAdvert('HP EliteBook Core i5 8GB 256GB SSD');
        ComputerDetail::updateOrCreate(['advert_id' => $advert->id], ['brand_id' => $this->other->id, 'ram' => '16GB']);

        $this->artisan('computers:backfill-specs')->assertSuccessful();

        $this->assertSame(
            ['processor' => 'Intel Core i5', 'ram' => '16GB', 'storage' => '256GB', 'storage_type' => 'SSD'],
            ComputerDetail::where('advert_id', $advert->id)->first()->only(['processor', 'ram', 'storage', 'storage_type'])
        );
    }
}
