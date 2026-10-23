<?php

namespace Tests\Feature\Sitemap;

use App\Models\Brands;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\VehicleModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GenerateSitemapExcludesCatchAllBrandsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAdvert(int $categoryId, SubCategory $subcat, int $brandId, string $stateSlug): int
    {
        $user = User::factory()->create();

        return DB::table('adverts')->insertGetId([
            'user_id' => $user->user_id,
            'ad_type' => 'Sell',
            'title_slug' => 'zz-sitemap-exclude-test-' . uniqid(),
            'category' => (string) $categoryId,
            'sub_category' => (string) $subcat->id,
            'brand' => (string) $brandId,
            'buy_direct' => 'No',
            'description' => 'test advert for sitemap catch-all exclusion',
            'state' => 'Addis Ababa',
            'lga' => 'Bole',
            'state_slug' => $stateSlug,
            'ad_status' => 'active',
            'views' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeAdvertWithModel(int $categoryId, SubCategory $subcat, int $brandId, int $modelId, string $stateSlug): void
    {
        $advertId = $this->makeAdvert($categoryId, $subcat, $brandId, $stateSlug);

        DB::table('car_details')->insert([
            'cat_id' => (string) $categoryId,
            'advert_id' => $advertId,
            'car_id' => 'ZZ' . uniqid(),
            'brand_id' => (string) $brandId,
            'model' => $modelId,
            'condition' => 'Locally Used',
            'registration' => 'Registered',
            'fuel' => 'Petrol',
            'transmission' => 'Automatic',
            'vehicle_type' => 'Saloon',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_catch_all_brand_pages_are_excluded_while_a_real_brand_still_appears(): void
    {
        $subcat = SubCategory::whereNotNull('cat_id')->first();
        $this->assertNotNull($subcat, 'Need at least 1 subcategory in database.');

        $otherBrand = Brands::where('brand_slug', 'other')->first();
        $this->assertNotNull($otherBrand, 'Expected the pre-existing catch-all "other" brand row.');

        $controlBrand = Brands::create(['subcat_id' => $subcat->id, 'brand' => 'Zz Sitemap Control Brand ' . uniqid()]);

        $stateSlug = 'zzsitemaptest' . substr(uniqid(), -8);

        // >=5 ads clears the sitemap's own indexability threshold, so any
        // difference in the two URLs' presence is purely down to the
        // catch-all exclusion, not the ad-count gate.
        for ($i = 0; $i < 5; $i++) {
            $this->makeAdvert($subcat->cat_id, $subcat, $otherBrand->id, $stateSlug);
            $this->makeAdvert($subcat->cat_id, $subcat, $controlBrand->id, $stateSlug);
        }

        Artisan::call('sitemap:generate');

        $locations = file_get_contents(storage_path('framework/testing/sitemap/sitemap-locations.xml'));

        $this->assertStringNotContainsString(
            "/{$stateSlug}/other",
            $locations,
            'Catch-all "other" brand page should not be in the sitemap regardless of ad count.'
        );
        $this->assertStringContainsString(
            "/{$stateSlug}/{$controlBrand->brand_slug}",
            $locations,
            'A real brand with >=5 ads should still be included.'
        );
    }

    public function test_catch_all_model_pages_are_excluded_while_a_real_model_still_appears(): void
    {
        $subcat = SubCategory::whereNotNull('cat_id')->first();
        $this->assertNotNull($subcat, 'Need at least 1 subcategory in database.');

        $category = Category::find($subcat->cat_id);
        $this->assertNotNull($category, 'Need a category matching the chosen subcategory.');

        $otherModel = VehicleModel::where('model_slug', 'other')->first();
        $this->assertNotNull($otherModel, 'Expected the pre-existing catch-all "other" model row.');

        $brand = Brands::create(['subcat_id' => $subcat->id, 'brand' => 'Zz Sitemap Model Brand ' . uniqid()]);
        $controlModel = VehicleModel::create(['subcat_id' => $subcat->id, 'brand_id' => $brand->id, 'model' => 'Zz Sitemap Control Model ' . uniqid()]);

        $stateSlug = 'zzsitemapmodeltest' . substr(uniqid(), -8);

        for ($i = 0; $i < 5; $i++) {
            $this->makeAdvertWithModel($category->id, $subcat, $brand->id, $otherModel->id, $stateSlug);
            $this->makeAdvertWithModel($category->id, $subcat, $brand->id, $controlModel->id, $stateSlug);
        }

        Artisan::call('sitemap:generate');

        $locations = file_get_contents(storage_path('framework/testing/sitemap/sitemap-locations.xml'));

        $this->assertStringNotContainsString(
            "/{$stateSlug}/{$category->category_slug}/other",
            $locations,
            'Catch-all "other" model page should not be in the sitemap regardless of ad count.'
        );
        $this->assertStringContainsString(
            "/{$stateSlug}/{$category->category_slug}/{$controlModel->model_slug}",
            $locations,
            'A real model with >=5 ads should still be included.'
        );
    }
}
