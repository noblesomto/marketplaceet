<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Advert;
use App\Models\Brands;
use App\Models\PhoneDetail;
use App\Models\SubCategory;
use App\Models\VehicleModel;
use App\Services\AdvertValidationService;
use App\Support\DeviceModels;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PhoneModelPagesTest extends TestCase
{
    use DatabaseTransactions;

    private SubCategory $phones;
    private Brands $brand;
    private VehicleModel $proMax;
    private VehicleModel $mini;

    protected function setUp(): void
    {
        parent::setUp();
        DeviceModels::flush();

        $phones = SubCategory::with('category')->find(DeviceModels::PHONES_SUBCATEGORY_ID);
        $template = Advert::where('ad_status', 'active')->where('sold', 'No')->first();

        if (!$phones || !$template) {
            $this->markTestSkipped('Needs the Mobile Phones subcategory and an active advert.');
        }

        $this->phones = $phones;
        $this->brand = Brands::create(['subcat_id' => $phones->id, 'brand' => 'Zz Test Phone ' . uniqid()]);
        $this->proMax = VehicleModel::create(['brand_id' => $this->brand->id, 'model' => 'Zz Phone 12 Pro Max ' . uniqid()]);
        $this->mini = VehicleModel::create(['brand_id' => $this->brand->id, 'model' => 'Zz Phone 12 mini ' . uniqid()]);

        $this->makeAdvert($template, 'Zz pro max listing one', $this->proMax);
        $this->makeAdvert($template, 'Zz pro max listing two', $this->proMax);
        $this->makeAdvert($template, 'Zz mini listing', $this->mini);
    }

    private function makeAdvert(Advert $template, string $title, VehicleModel $model): Advert
    {
        $advert = $template->replicate(['title_slug']);
        $advert->ad_id = random_int(10000000, 99999999);
        $advert->ad_title = $title;
        $advert->title_slug = \Illuminate\Support\Str::slug($title) . '-' . uniqid();
        $advert->category = $this->phones->cat_id;
        $advert->sub_category = $this->phones->id;
        $advert->brand = $this->brand->id;
        $advert->featured = 'No';
        $advert->save();

        $phone = new PhoneDetail([
            'phone_id'  => random_int(10000, 99999),
            'cat_id'    => $this->phones->cat_id,
            'brand_id'  => $this->brand->id,
            'model'     => $model->id,
            'color'     => 'Black',
            'device'    => 'Device',
            'condition' => 'Used - Very Good',
        ]);
        $phone->advert()->associate($advert);
        $phone->save();

        return $advert;
    }

    private function brandUrl(): string
    {
        return "/category/{$this->phones->category->category_slug}/{$this->phones->sub_cat_slug}/{$this->brand->brand_slug}";
    }

    public function test_brand_page_lists_models_in_shop_by_model(): void
    {
        $response = $this->get($this->brandUrl());

        $response->assertOk();
        $response->assertSee('Shop by Model');
        $response->assertSee($this->brandUrl() . '/' . $this->proMax->model_slug, false);
        $response->assertSee($this->brandUrl() . '/' . $this->mini->model_slug, false);
    }

    public function test_model_page_only_shows_that_models_listings(): void
    {
        $response = $this->get($this->brandUrl() . '/' . $this->proMax->model_slug);

        $response->assertOk();
        $response->assertSee('Zz pro max listing one');
        $response->assertSee('Zz pro max listing two');
        $response->assertDontSee('Zz mini listing');
        $response->assertSee('aria-current="page"', false);
    }

    public function test_unknown_model_slug_is_404(): void
    {
        $this->get($this->brandUrl() . '/no-such-model-' . uniqid())->assertNotFound();
    }

    public function test_another_brands_model_slug_is_404(): void
    {
        $other = Brands::create(['subcat_id' => $this->phones->id, 'brand' => 'Zz Other Phone ' . uniqid()]);
        $foreign = VehicleModel::create(['brand_id' => $other->id, 'model' => 'Zz Foreign ' . uniqid()]);

        $this->get($this->brandUrl() . '/' . $foreign->model_slug)->assertNotFound();
    }

    public function test_load_more_and_filters_respect_the_model(): void
    {
        $html = $this->getJson('/adverts/load-more?' . http_build_query([
            'page' => 1, 'brand' => $this->brand->id, 'model' => $this->mini->id,
        ]))->assertOk()->json('html');

        $this->assertStringContainsString('Zz mini listing', $html);
        $this->assertStringNotContainsString('Zz pro max listing', $html);

        $html = $this->postJson('/filter/adverts', [
            'brand' => $this->brand->id, 'model' => $this->proMax->id,
        ])->assertOk()->json('html');

        $this->assertStringContainsString('Zz pro max listing one', $html);
        $this->assertStringNotContainsString('Zz mini listing', $html);
    }

    public function test_web_rules_require_a_model_for_a_brand_with_models(): void
    {
        $rules = ['model' => (new AdvertValidationService())->getRules($this->phones->cat_id, $this->phones->id, true, false, null, $this->brand->id)['model']];

        $this->assertTrue(Validator::make(['model' => ''], $rules)->fails());
        $this->assertTrue(Validator::make(['model' => '999999999'], $rules)->fails());
        $this->assertFalse(Validator::make(['model' => (string) $this->mini->id], $rules)->fails());
        $this->assertFalse(Validator::make(['model' => DeviceModels::OTHER], $rules)->fails());
    }

    public function test_api_rules_make_the_model_optional(): void
    {
        $rules = ['model' => (new AdvertValidationService())->getRules($this->phones->cat_id, $this->phones->id, true, false, null, $this->brand->id, false)['model']];

        $this->assertFalse(Validator::make([], $rules)->fails());
        $this->assertTrue(Validator::make(['model' => '999999999'], $rules)->fails());
    }

    public function test_brand_without_models_never_requires_one(): void
    {
        $plain = Brands::create(['subcat_id' => $this->phones->id, 'brand' => 'Zz Plain Phone ' . uniqid()]);

        $rules = (new AdvertValidationService())->getRules($this->phones->cat_id, $this->phones->id, true, false, null, $plain->id);

        $this->assertSame('nullable', $rules['model']);
    }

    public function test_resolve_stores_other_as_no_model_and_infers_from_title_for_the_app(): void
    {
        $this->assertSame($this->mini->id, DeviceModels::resolve($this->brand->id, (string) $this->mini->id));
        $this->assertNull(DeviceModels::resolve($this->brand->id, DeviceModels::OTHER));
        $this->assertNull(DeviceModels::resolve($this->brand->id, DeviceModels::OTHER, $this->mini->model));

        // A model from another brand is never stored.
        $other = Brands::create(['subcat_id' => $this->phones->id, 'brand' => 'Zz Other Phone ' . uniqid()]);
        $foreign = VehicleModel::create(['brand_id' => $other->id, 'model' => 'Zz Foreign ' . uniqid()]);
        $this->assertNull(DeviceModels::resolve($this->brand->id, (string) $foreign->id));

        // App: no model sent → inferred from the title.
        $this->assertSame($this->proMax->id, DeviceModels::resolve($this->brand->id, null, 'Clean ' . $this->proMax->model . ' 256gb'));
    }
}
