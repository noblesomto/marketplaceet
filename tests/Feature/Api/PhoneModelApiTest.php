<?php

namespace Tests\Feature\Api;

use App\Models\Advert;
use App\Models\Brands;
use App\Models\Lga;
use App\Models\PhoneDetail;
use App\Models\State;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\VehicleModel;
use App\Support\DeviceModels;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Phone models (e.g. Apple → iPhone 12 Pro Max) through the mobile API:
 * browsing by model, filtering, the picker metadata, and create/update.
 */
class PhoneModelApiTest extends TestCase
{
    use DatabaseTransactions;

    private SubCategory $phones;
    private Brands $brand;
    private VehicleModel $proMax;
    private VehicleModel $mini;
    private array $createdAdvertIds = [];

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
        $this->brand = Brands::create(['subcat_id' => $phones->id, 'brand' => 'Zz Api Phone ' . uniqid()]);
        $this->proMax = VehicleModel::create(['brand_id' => $this->brand->id, 'model' => 'Zz Handset 12 Pro Max']);
        $this->mini = VehicleModel::create(['brand_id' => $this->brand->id, 'model' => 'Zz Handset 12 mini']);

        $this->makeAdvert($template, 'Zz api pro max one', $this->proMax, 'Addis Ababa');
        $this->makeAdvert($template, 'Zz api pro max two', $this->proMax, 'Abia');
        $this->makeAdvert($template, 'Zz api mini', $this->mini, 'Addis Ababa');
    }

    protected function tearDown(): void
    {
        // Adverts created through the API commit outside the test transaction.
        foreach (Advert::whereIn('id', $this->createdAdvertIds)->get() as $advert) {
            $advert->phone?->delete();
            $advert->clearMediaCollection('images');
            $advert->delete();
        }

        parent::tearDown();
    }

    private function makeAdvert(Advert $template, string $title, VehicleModel $model, string $state): Advert
    {
        $advert = $template->replicate(['title_slug']);
        $advert->ad_id = random_int(10000000, 99999999);
        $advert->ad_title = $title;
        $advert->title_slug = Str::slug($title) . '-' . uniqid();
        $advert->category = $this->phones->cat_id;
        $advert->sub_category = $this->phones->id;
        $advert->brand = $this->brand->id;
        $advert->state = $state;
        $advert->featured = 'No';
        $advert->save();

        $phone = new PhoneDetail([
            'phone_id' => random_int(10000, 99999), 'cat_id' => $this->phones->cat_id, 'brand_id' => $this->brand->id,
            'model' => $model->id, 'color' => 'Black', 'device' => 'Device', 'condition' => 'Used - Very Good',
        ]);
        $phone->advert()->associate($advert);
        $phone->save();

        return $advert;
    }

    private function brandPath(): string
    {
        return "/api/brands/{$this->phones->category->category_slug}/{$this->phones->sub_cat_slug}/{$this->brand->brand_slug}";
    }

    private function titles($response, string $path = 'data.ads'): array
    {
        return collect($response->json($path))->pluck('ad_title')->all();
    }

    // ── Browsing ──────────────────────────────────────────────────────────

    public function test_brand_endpoint_returns_shop_by_model_list(): void
    {
        $response = $this->getJson($this->brandPath())->assertOk();

        $models = collect($response->json('data.models'))->keyBy('model_slug');
        $this->assertSame(2, $models[$this->proMax->model_slug]['advert_count']);
        $this->assertSame(1, $models[$this->mini->model_slug]['advert_count']);
        $this->assertNull($response->json('data.model'));
        $this->assertSame(3, $response->json('pagination.total'));
    }

    public function test_model_endpoint_lists_only_that_model(): void
    {
        $response = $this->getJson($this->brandPath() . '/' . $this->proMax->model_slug)->assertOk();

        $this->assertEqualsCanonicalizing(['Zz api pro max one', 'Zz api pro max two'], $this->titles($response));
        $this->assertSame($this->proMax->id, $response->json('data.model.id'));
        $this->assertSame(2, $response->json('pagination.total'));
    }

    public function test_brand_endpoint_model_query_accepts_slug_or_id(): void
    {
        $bySlug = $this->getJson($this->brandPath() . '?model=' . $this->mini->model_slug)->assertOk();
        $byId = $this->getJson($this->brandPath() . '?model=' . $this->mini->id)->assertOk();

        $this->assertSame(['Zz api mini'], $this->titles($bySlug));
        $this->assertSame(['Zz api mini'], $this->titles($byId));
    }

    public function test_unknown_or_foreign_model_is_404(): void
    {
        $other = Brands::create(['subcat_id' => $this->phones->id, 'brand' => 'Zz Api Other ' . uniqid()]);
        $foreign = VehicleModel::create(['brand_id' => $other->id, 'model' => 'Zz Foreign Phone']);

        $this->getJson($this->brandPath() . '/no-such-model')->assertNotFound()->assertJson(['message' => 'Model not found']);
        $this->getJson($this->brandPath() . '/' . $foreign->model_slug)->assertNotFound();
    }

    public function test_search_filter_and_load_more_accept_model(): void
    {
        $filter = $this->postJson('/api/search/filter', ['brand' => $this->brand->id, 'model' => $this->mini->id])->assertOk();
        $this->assertSame(['Zz api mini'], $this->titles($filter, 'data'));
        $this->assertEquals($this->mini->id, $filter->json('filters_applied.model'));

        $several = $this->postJson('/api/search/filter', ['brand' => $this->brand->id, 'model' => [$this->mini->id, $this->proMax->id]])->assertOk();
        $this->assertCount(3, $several->json('data'));

        $loadMore = $this->getJson('/api/adverts/load-more?' . http_build_query(['brand' => $this->brand->id, 'model' => $this->proMax->id]))->assertOk();
        $this->assertSame(2, $loadMore->json('pagination.total'));

        $searchLoadMore = $this->getJson('/api/search/load-more?' . http_build_query(['brand' => $this->brand->id, 'model' => $this->mini->id]))->assertOk();
        $this->assertSame(['Zz api mini'], $this->titles($searchLoadMore, 'data'));
    }

    public function test_location_search_resolves_model_slugs(): void
    {
        $response = $this->getJson('/api/search/location/Addis%20Ababa/' . $this->proMax->model_slug)->assertOk();

        $this->assertSame(['Zz api pro max one'], $this->titles($response, 'data'));
        $this->assertSame($this->proMax->id, $response->json('model.id'));
        $this->assertSame(2, $response->json('total_count'));
    }

    public function test_search_filters_lists_models_for_a_brand(): void
    {
        $models = $this->getJson('/api/search/filters?brand=' . $this->brand->id)->assertOk()->json('data.models');

        $this->assertEqualsCanonicalizing([$this->proMax->model_slug, $this->mini->model_slug], array_column($models, 'model_slug'));
        $this->assertSame([], $this->getJson('/api/search/filters')->assertOk()->json('data.models'));
    }

    // ── Model picker metadata ─────────────────────────────────────────────

    public function test_models_endpoint_says_how_to_render_the_picker(): void
    {
        $response = $this->getJson("/api/adverts/brands/{$this->brand->id}/models")->assertOk();

        $this->assertSame([$this->proMax->id, $this->mini->id], array_column($response->json('data'), 'id'));
        $this->assertSame(['show' => true, 'required' => true, 'other_option' => ['value' => 'other', 'label' => 'Other / not listed']], $response->json('model_field'));

        $plain = Brands::create(['subcat_id' => $this->phones->id, 'brand' => 'Zz Api Plain ' . uniqid()]);
        $this->assertSame(['show' => false, 'required' => false, 'other_option' => null],
            $this->getJson("/api/adverts/brands/{$plain->id}/models")->json('model_field'));
    }

    // ── Create / update ───────────────────────────────────────────────────

    private function seller(): User
    {
        $user = User::first();
        if (empty($user->phone)) {
            $user->phone = '08012345678';
            $user->save();
        }

        return $user;
    }

    private function photo(int $n): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'phone') . '.jpg';
        $im = imagecreatetruecolor(1200, 900);
        for ($y = 0; $y < 900; $y += 6) {
            for ($x = 0; $x < 1200; $x += 6) {
                imagefilledrectangle($im, $x, $y, $x + 5, $y + 5, imagecolorallocate($im, ($x * $n ^ $y) % 256, ($y + $n * 40) % 256, mt_rand(0, 255)));
            }
        }
        imagejpeg($im, $path, 92);

        return new UploadedFile($path, "phone-{$n}.jpg", 'image/jpeg', null, true);
    }

    /** Letters only — digit runs in a title trip the "contact details" filter. */
    private function tag(): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 8);
    }

    private function phonePayload(array $overrides = []): array
    {
        $state = State::where('name', 'Addis Ababa')->first() ?? State::first();
        $lga = Lga::where('state_id', $state->id)->value('name') ?? Lga::value('name');

        return array_merge([
            'ad_title'        => 'Clean Zz Handset 12 Pro Max for sale ' . $this->tag(),
            'ad_type'         => 'Private',
            'category'        => $this->phones->cat_id,
            'subcategory'     => $this->phones->id,
            'brand'           => $this->brand->id,
            'price'           => 650000,
            'price_type'      => 'Fixed',
            'description'     => 'API test handset listing for model support. Neat and working perfectly.',
            'phone_color'     => 'Black',
            'phone_condition' => 'Used - Very Good',
            'device'          => 'Device',
            'shipment'        => 'Pickup',
            'buy_direct'      => 'No',
            'show_contact'    => 'Yes',
            'state'           => $state->name,
            'lga'             => $lga,
            'images'          => [$this->photo(1), $this->photo(2), $this->photo(3)],
        ], $overrides);
    }

    private function createAdvert(array $overrides = [])
    {
        $response = $this->post('/api/adverts', $this->phonePayload($overrides), ['Accept' => 'application/json']);

        if ($id = $response->json('data.advert.id') ?? $response->json('data.id')) {
            $this->createdAdvertIds[] = $id;
        }

        return $response;
    }

    private function storedModel($response): ?int
    {
        $id = $response->json('data.advert.id') ?? $response->json('data.id');
        $model = PhoneDetail::where('advert_id', $id)->value('model');

        return $model === null ? null : (int) $model;
    }

    public function test_create_stores_the_chosen_model(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);

        $response = $this->createAdvert(['model' => $this->mini->id]);

        $response->assertSuccessful();
        $this->assertSame($this->mini->id, $this->storedModel($response));
    }

    public function test_create_without_model_infers_it_from_the_title(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);

        $response = $this->createAdvert();

        $response->assertSuccessful();
        $this->assertSame($this->proMax->id, $this->storedModel($response));
    }

    public function test_create_with_other_stores_no_model(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);

        $response = $this->createAdvert(['model' => 'other']);

        $response->assertSuccessful();
        $this->assertNull($this->storedModel($response));
    }

    public function test_create_rejects_a_model_from_another_brand(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);
        $other = Brands::create(['subcat_id' => $this->phones->id, 'brand' => 'Zz Api Other ' . uniqid()]);
        $foreign = VehicleModel::create(['brand_id' => $other->id, 'model' => 'Zz Foreign Phone']);

        $this->createAdvert(['model' => $foreign->id])->assertStatus(422)->assertJsonValidationErrors('model');
    }

    public function test_update_without_model_keeps_the_existing_one(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);
        $created = $this->createAdvert(['model' => $this->mini->id, 'ad_title' => 'Plain title with no model ' . $this->tag()]);
        $id = $created->json('data.advert.id') ?? $created->json('data.id');

        $payload = $this->phonePayload(['ad_title' => 'Updated plain title ' . $this->tag()]);
        unset($payload['images']);
        $this->put("/api/adverts/{$id}", $payload, ['Accept' => 'application/json'])->assertSuccessful();

        $this->assertSame($this->mini->id, (int) PhoneDetail::where('advert_id', $id)->value('model'));

        $edit = $this->getJson("/api/adverts/{$id}/edit")->assertOk();
        $this->assertSame($this->mini->id, (int) $edit->json('data.advert.phone.model'));
        $this->assertTrue($edit->json('data.model_field.required'));
    }
}
