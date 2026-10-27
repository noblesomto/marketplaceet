<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Advert;
use App\Models\Brands;
use App\Models\ComputerDetail;
use App\Models\SubCategory;
use App\Models\VehicleModel;
use App\Services\AdvertValidationService;
use App\Support\DeviceModels;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Laptops & Computers: makers as brands, product lines as models
 * (computers:restructure), and the pages/forms that use them.
 */
class ComputerModelsTest extends TestCase
{
    use DatabaseTransactions;

    private SubCategory $computers;
    private Advert $template;

    protected function setUp(): void
    {
        parent::setUp();
        DeviceModels::flush();

        $computers = SubCategory::with('category')->find(DeviceModels::COMPUTERS_SUBCATEGORY_ID);
        $template = Advert::where('ad_status', 'active')->where('sold', 'No')->first();

        if (!$computers || !$template || !Brands::where('subcat_id', $computers->id)->where('brand', 'Other')->exists()) {
            $this->markTestSkipped('Needs the Laptops & Computers subcategory (with its Other brand) and an active advert.');
        }

        $this->computers = $computers;
        $this->template = $template;
    }

    private function makeAdvert(string $title, Brands $brand): Advert
    {
        $advert = $this->template->replicate(['title_slug']);
        $advert->ad_id = random_int(10000000, 99999999);
        $advert->ad_title = $title;
        $advert->title_slug = Str::slug($title) . '-' . uniqid();
        $advert->category = $this->computers->cat_id;
        $advert->sub_category = $this->computers->id;
        $advert->brand = $brand->id;
        $advert->featured = 'No';
        $advert->save();

        return $advert;
    }

    private function restructureWithTypeBrand(): array
    {
        $laptop = Brands::where('subcat_id', $this->computers->id)->where('brand', 'Laptop')->first()
            ?? Brands::create(['subcat_id' => $this->computers->id, 'brand' => 'Laptop']);

        $elitebook = $this->makeAdvert('Foreign used HP Elitebook 840 G5 Core i5', $laptop);
        $latitude = $this->makeAdvert('Clean Dell Latitude5410 with charger', $laptop);
        $unknown = $this->makeAdvert('UK used laptops available', $laptop);

        $this->artisan('computers:restructure')->assertSuccessful();
        DeviceModels::flush();

        return [$laptop, $elitebook->fresh(), $latitude->fresh(), $unknown->fresh()];
    }

    private function brand(string $maker): Brands
    {
        return Brands::where('subcat_id', $this->computers->id)->where('brand', $maker)->firstOrFail();
    }

    public function test_restructure_files_listings_under_their_maker_and_product_line(): void
    {
        [, $elitebook, $latitude, $unknown] = $this->restructureWithTypeBrand();

        $hp = $this->brand('HP');
        $this->assertSame($hp->id, (int) $elitebook->brand);
        $this->assertSame('hp-elitebook', VehicleModel::find($elitebook->computer->model)->model_slug);

        $this->assertSame($this->brand('Dell')->id, (int) $latitude->brand);
        $this->assertSame('Latitude', VehicleModel::find($latitude->computer->model)->model);

        $this->assertSame($this->brand('Other')->id, (int) $unknown->brand);
        $this->assertNull($unknown->computer->model);
    }

    public function test_restructure_retires_type_brands_and_is_safe_to_rerun(): void
    {
        [$laptop, $elitebook] = $this->restructureWithTypeBrand();

        $this->assertNull(Brands::find($laptop->id));
        $modelCount = VehicleModel::where('brand_id', $this->brand('HP')->id)->count();

        $this->artisan('computers:restructure')->assertSuccessful();

        $this->assertSame($modelCount, VehicleModel::where('brand_id', $this->brand('HP')->id)->count());
        $this->assertSame(1, ComputerDetail::where('advert_id', $elitebook->id)->count());
    }

    public function test_retired_brand_url_redirects_to_the_subcategory(): void
    {
        $this->restructureWithTypeBrand();
        $base = "/category/{$this->computers->category->category_slug}/{$this->computers->sub_cat_slug}";

        $this->get("{$base}/laptop")->assertRedirect($base)->assertStatus(301);
    }

    public function test_brand_and_model_pages_list_the_listing(): void
    {
        [, $elitebook] = $this->restructureWithTypeBrand();
        $hp = $this->brand('HP');
        $base = "/category/{$this->computers->category->category_slug}/{$this->computers->sub_cat_slug}/{$hp->brand_slug}";

        $this->get($base)->assertOk()->assertSee('Shop by Model')->assertSee('EliteBook');
        $this->get("{$base}/hp-elitebook")->assertOk()->assertSee($elitebook->ad_title, false);
    }

    public function test_web_rules_require_a_model_for_a_maker_with_models(): void
    {
        $this->restructureWithTypeBrand();
        $hp = $this->brand('HP');

        $rules = (new AdvertValidationService())->getRules($this->computers->cat_id, $this->computers->id, false, false, 'Private', $hp->id);

        $this->assertTrue(Validator::make(['model' => ''], ['model' => $rules['model']])->fails());
        $this->assertFalse(Validator::make(['model' => 'other'], ['model' => $rules['model']])->fails());
        $this->assertSame('Other / not listed', DeviceModels::fieldFor($hp)['other_option']['label']);
    }

    public function test_app_listings_get_the_model_from_the_title(): void
    {
        $this->restructureWithTypeBrand();
        $advert = $this->makeAdvert('Lenovo Thinkpad T480 for sale', $this->brand('Lenovo'));

        ComputerDetail::syncFor($advert, $advert->brand, null, $advert->ad_title);

        $this->assertSame('ThinkPad', VehicleModel::find($advert->computer()->first()->model)->model);
    }
}
