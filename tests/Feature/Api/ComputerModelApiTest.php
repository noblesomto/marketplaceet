<?php

namespace Tests\Feature\Api;

use App\Models\Advert;
use App\Models\Brands;
use App\Models\ComputerDetail;
use App\Models\Lga;
use App\Models\State;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\VehicleModel;
use App\Support\ComputerSpecs;
use App\Support\DeviceModels;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Laptop models (e.g. HP → EliteBook) and specs through the mobile API:
 * browsing by model, filtering, the picker metadata, and create/update/edit.
 */
class ComputerModelApiTest extends TestCase
{
    use DatabaseTransactions;

    private SubCategory $computers;
    private Brands $brand;
    private VehicleModel $elite;
    private VehicleModel $pro;
    private array $createdAdvertIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        DeviceModels::flush();

        $computers = SubCategory::with('category')->find(DeviceModels::COMPUTERS_SUBCATEGORY_ID);
        $template = Advert::where('ad_status', 'active')->where('sold', 'No')->first();

        if (!$computers || !$template) {
            $this->markTestSkipped('Needs the Laptops & Computers subcategory and an active advert.');
        }

        $this->computers = $computers;
        $this->brand = Brands::create(['subcat_id' => $computers->id, 'brand' => 'Zz Api Maker ' . uniqid()]);
        $this->elite = VehicleModel::create(['brand_id' => $this->brand->id, 'model' => 'Zzelite']);
        $this->pro = VehicleModel::create(['brand_id' => $this->brand->id, 'model' => 'Zzpro']);

        $this->makeAdvert($template, 'Zz api zzelite one', $this->elite, ['ram' => '16GB']);
        $this->makeAdvert($template, 'Zz api zzelite two', $this->elite, ['ram' => '8GB']);
        $this->makeAdvert($template, 'Zz api zzpro', $this->pro, ['ram' => '16GB']);
    }

    protected function tearDown(): void
    {
        // Adverts created through the API commit outside the test transaction.
        foreach (Advert::whereIn('id', $this->createdAdvertIds)->get() as $advert) {
            $advert->computer?->delete();
            $advert->clearMediaCollection('images');
            $advert->delete();
        }

        parent::tearDown();
    }

    private function makeAdvert(Advert $template, string $title, VehicleModel $model, array $specs): Advert
    {
        $advert = $template->replicate(['title_slug']);
        $advert->ad_id = random_int(10000000, 99999999);
        $advert->ad_title = $title;
        $advert->title_slug = Str::slug($title) . '-' . uniqid();
        $advert->category = $this->computers->cat_id;
        $advert->sub_category = $this->computers->id;
        $advert->brand = $this->brand->id;
        $advert->featured = 'No';
        $advert->save();

        ComputerDetail::create(['advert_id' => $advert->id, 'brand_id' => $this->brand->id, 'model' => $model->id] + $specs);

        return $advert;
    }

    private function brandPath(): string
    {
        return "/api/brands/{$this->computers->category->category_slug}/{$this->computers->sub_cat_slug}/{$this->brand->brand_slug}";
    }

    private function titles($response, string $path = 'data.ads'): array
    {
        return collect($response->json($path))->pluck('ad_title')->all();
    }

    // ── Browsing ──────────────────────────────────────────────────────────

    public function test_brand_and_model_endpoints_list_laptops_by_model(): void
    {
        $brand = $this->getJson($this->brandPath())->assertOk();
        $models = collect($brand->json('data.models'))->keyBy('model_slug');
        $this->assertSame(2, $models[$this->elite->model_slug]['advert_count']);
        $this->assertSame(3, $brand->json('pagination.total'));

        $model = $this->getJson($this->brandPath() . '/' . $this->elite->model_slug)->assertOk();
        $this->assertEqualsCanonicalizing(['Zz api zzelite one', 'Zz api zzelite two'], $this->titles($model));
        $this->assertSame($this->elite->id, $model->json('data.model.id'));
    }

    public function test_search_filter_accepts_model_and_specs(): void
    {
        $byModel = $this->postJson('/api/search/filter', ['brand' => $this->brand->id, 'model' => $this->pro->id])->assertOk();
        $this->assertSame(['Zz api zzpro'], $this->titles($byModel, 'data'));

        $bySpec = $this->postJson('/api/search/filter', ['brand' => $this->brand->id, 'model' => $this->elite->id, 'ram' => ['16GB']])->assertOk();
        $this->assertSame(['Zz api zzelite one'], $this->titles($bySpec, 'data'));
        $this->assertSame(['16GB'], $bySpec->json('filters_applied.ram'));
    }

    public function test_search_filters_and_models_endpoint_cover_laptop_brands(): void
    {
        $models = $this->getJson('/api/search/filters?brand=' . $this->brand->id)->assertOk()->json('data.models');
        $this->assertEqualsCanonicalizing([$this->elite->model_slug, $this->pro->model_slug], array_column($models, 'model_slug'));

        $picker = $this->getJson("/api/adverts/brands/{$this->brand->id}/models")->assertOk();
        $this->assertSame([$this->elite->id, $this->pro->id], array_column($picker->json('data'), 'id'));
        $this->assertSame(['show' => true, 'required' => true, 'other_option' => ['value' => 'other', 'label' => 'Other / not listed']], $picker->json('model_field'));
    }

    public function test_advert_detail_returns_model_and_specs(): void
    {
        $advert = Advert::where('brand', $this->brand->id)->where('ad_title', 'Zz api zzpro')->first();

        $detail = $this->getJson("/api/adverts/{$advert->id}")->assertOk();

        $this->assertSame('Zzpro', $detail->json('data.model.model'));
        $this->assertSame('16GB', $detail->json('data.computer.ram'));
    }

    // ── Create / update / edit ────────────────────────────────────────────

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
        $path = tempnam(sys_get_temp_dir(), 'laptop') . '.jpg';
        $im = imagecreatetruecolor(1200, 900);
        for ($y = 0; $y < 900; $y += 6) {
            for ($x = 0; $x < 1200; $x += 6) {
                imagefilledrectangle($im, $x, $y, $x + 5, $y + 5, imagecolorallocate($im, ($x * $n ^ $y) % 256, ($y + $n * 40) % 256, mt_rand(0, 255)));
            }
        }
        imagejpeg($im, $path, 92);

        return new UploadedFile($path, "laptop-{$n}.jpg", 'image/jpeg', null, true);
    }

    /** Letters only — digit runs in a title trip the "contact details" filter. */
    private function tag(): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 8);
    }

    private function laptopPayload(array $overrides = []): array
    {
        $state = State::where('name', 'Addis Ababa')->first() ?? State::first();
        $lga = Lga::where('state_id', $state->id)->value('name') ?? Lga::value('name');

        return array_merge([
            'ad_title'       => 'Clean Zzelite laptop Core i5 for sale ' . $this->tag(),
            'ad_type'        => 'Private',
            'category'       => $this->computers->cat_id,
            'subcategory'    => $this->computers->id,
            'brand'          => $this->brand->id,
            'item_condition' => 'Foreign Used',
            'price'          => 350000,
            'price_type'     => 'Fixed',
            'description'    => 'API test laptop listing. Core i5, 8GB RAM and 256GB SSD, keyboard light, neat.',
            'shipment'       => 'Pickup',
            'buy_direct'     => 'No',
            'show_contact'   => 'Yes',
            'state'          => $state->name,
            'lga'            => $lga,
            'images'         => [$this->photo(1), $this->photo(2), $this->photo(3)],
        ], $overrides);
    }

    private function createAdvert(array $overrides = [])
    {
        $response = $this->post('/api/adverts', $this->laptopPayload($overrides), ['Accept' => 'application/json']);

        if ($id = $response->json('data.advert.id') ?? $response->json('data.id')) {
            $this->createdAdvertIds[] = $id;
        }

        return $response;
    }

    private function stored($response): ComputerDetail
    {
        return ComputerDetail::where('advert_id', $response->json('data.advert.id') ?? $response->json('data.id'))->firstOrFail();
    }

    public function test_create_data_lists_the_spec_options(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);

        $options = $this->getJson('/api/adverts/create/data')->assertOk()->json('data.computer_options');

        $this->assertSame(ComputerSpecs::allOptions(), $options);
    }

    public function test_create_stores_the_chosen_model_and_specs(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);

        $response = $this->createAdvert(['model' => $this->pro->id, 'processor' => 'AMD Ryzen 5', 'ram' => '16GB', 'storage' => '512GB', 'storage_type' => 'SSD']);

        $response->assertSuccessful();
        $this->assertSame(
            ['model' => $this->pro->id, 'processor' => 'AMD Ryzen 5', 'ram' => '16GB', 'storage' => '512GB', 'storage_type' => 'SSD'],
            $this->stored($response)->only(['model', 'processor', 'ram', 'storage', 'storage_type'])
        );
    }

    public function test_create_without_model_or_specs_reads_them_from_the_listing(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);

        $response = $this->createAdvert();

        $response->assertSuccessful();
        $this->assertSame(
            ['model' => $this->elite->id, 'processor' => 'Intel Core i5', 'ram' => '8GB', 'storage' => '256GB', 'storage_type' => 'SSD'],
            $this->stored($response)->only(['model', 'processor', 'ram', 'storage', 'storage_type'])
        );
    }

    public function test_create_rejects_an_unlisted_spec_value(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);

        $this->createAdvert(['ram' => '7GB'])->assertStatus(422)->assertJsonValidationErrors('ram');
    }

    public function test_update_without_model_or_specs_keeps_them_and_edit_returns_them(): void
    {
        Sanctum::actingAs($this->seller(), ['*']);
        $created = $this->createAdvert(['model' => $this->pro->id, 'ram' => '32GB', 'processor' => 'Intel Core i9']);
        $id = $created->json('data.advert.id') ?? $created->json('data.id');

        $payload = $this->laptopPayload(['ad_title' => 'Updated plain laptop ' . $this->tag(), 'description' => 'Updated listing text, still neat and working.']);
        unset($payload['images']);
        $this->put("/api/adverts/{$id}", $payload, ['Accept' => 'application/json'])->assertSuccessful();

        $detail = ComputerDetail::where('advert_id', $id)->first();
        $this->assertSame([$this->pro->id, '32GB', 'Intel Core i9'], [$detail->model, $detail->ram, $detail->processor]);

        $edit = $this->getJson("/api/adverts/{$id}/edit")->assertOk();
        $this->assertSame($this->pro->id, (int) $edit->json('data.advert.computer.model'));
        $this->assertSame('32GB', $edit->json('data.advert.computer.ram'));
        $this->assertSame([$this->elite->id, $this->pro->id], array_column($edit->json('data.models'), 'id'));
        $this->assertTrue($edit->json('data.model_field.required'));
    }
}
