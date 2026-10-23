<?php

namespace Tests\Feature\Taxonomy;

use App\Models\Brands;
use App\Models\VehicleModel;
use App\Services\SlugRedirectResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SlugRedirectRoutesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_flat_location_brand_url_redirects_to_new_slug(): void
    {
        $brand = Brands::whereNotNull('brand_slug')->first();

        if (!$brand) {
            $this->markTestSkipped('No brands in database.');
        }

        (new SlugRedirectResolver())->record('brand', 'zz-old-brand-slug', $brand->brand_slug);

        $response = $this->get('/lagos/zz-old-brand-slug');

        $response->assertStatus(301)
                 ->assertRedirect('/lagos/' . $brand->brand_slug);
    }

    public function test_flat_location_category_brand_url_redirects_to_new_slug(): void
    {
        $brand = Brands::with('subCategory.category')->whereHas('subCategory.category')->first();

        if (!$brand || !$brand->subCategory || !$brand->subCategory->category) {
            $this->markTestSkipped('No brand with full category/subcategory chain in database.');
        }

        $cat = $brand->subCategory->category;

        (new SlugRedirectResolver())->record('brand', 'zz-old-cat-brand-slug', $brand->brand_slug);

        $response = $this->get("/lagos/{$cat->category_slug}/zz-old-cat-brand-slug");

        $response->assertStatus(301)
                 ->assertRedirect("/lagos/{$cat->category_slug}/{$brand->brand_slug}");
    }

    public function test_unknown_slug_without_redirect_still_404s(): void
    {
        $response = $this->get('/lagos/zz-totally-unknown-slug-xyz');

        $response->assertStatus(404);
    }

    public function test_nested_category_subcat_brand_url_redirects_to_new_slug(): void
    {
        $brand = \App\Models\Brands::with('subCategory.category')->whereHas('subCategory.category')->first();

        if (!$brand || !$brand->subCategory || !$brand->subCategory->category) {
            $this->markTestSkipped('No brand with full category/subcategory chain in database.');
        }

        $cat = $brand->subCategory->category;
        $subcat = $brand->subCategory;

        (new SlugRedirectResolver())->record('brand', 'zz-old-nested-brand-slug', $brand->brand_slug);

        $response = $this->get("/category/{$cat->category_slug}/{$subcat->sub_cat_slug}/zz-old-nested-brand-slug");

        $response->assertStatus(301)
                 ->assertRedirect("/category/{$cat->category_slug}/{$subcat->sub_cat_slug}/{$brand->brand_slug}");
    }

    public function test_nested_subcategory_url_redirects_to_new_slug(): void
    {
        $subcat = \App\Models\SubCategory::whereHas('category')->first();

        if (!$subcat || !$subcat->category) {
            $this->markTestSkipped('No subcategory with a category in database.');
        }

        (new SlugRedirectResolver())->record('subcategory', 'zz-old-nested-subcat-slug', $subcat->sub_cat_slug);

        $response = $this->get("/category/{$subcat->category->category_slug}/zz-old-nested-subcat-slug");

        $response->assertStatus(301)
                 ->assertRedirect("/category/{$subcat->category->category_slug}/{$subcat->sub_cat_slug}");
    }

    public function test_flat_location_category_model_url_redirects_to_new_slug(): void
    {
        $model = VehicleModel::with('brand.subCategory.category')
            ->whereHas('brand.subCategory.category')
            ->whereHas('brand.subCategory', fn ($q) => $q->whereIn('cat_id', [1, 4]))
            ->first();

        if (!$model || !$model->brand || !$model->brand->subCategory || !$model->brand->subCategory->category) {
            $this->markTestSkipped('No vehicle model with full brand/subcategory/category chain in a model-bearing category (vehicles/phones) in database.');
        }

        $cat = $model->brand->subCategory->category;

        (new SlugRedirectResolver())->record('model', 'zz-old-model-slug', $model->model_slug);

        $response = $this->get("/lagos/{$cat->category_slug}/zz-old-model-slug");

        $response->assertStatus(301)
                 ->assertRedirect("/lagos/{$cat->category_slug}/{$model->model_slug}");
    }

    public function test_model_redirect_does_not_apply_across_wrong_category(): void
    {
        $model = VehicleModel::with('brand.subCategory.category')
            ->whereHas('brand.subCategory.category')
            ->whereHas('brand.subCategory', fn ($q) => $q->whereIn('cat_id', [1, 4]))
            ->first();

        if (!$model || !$model->brand || !$model->brand->subCategory || !$model->brand->subCategory->category) {
            $this->markTestSkipped('No vehicle model with full brand/subcategory/category chain in a model-bearing category (vehicles/phones) in database.');
        }

        $ownCatId = $model->brand->subCategory->cat_id;
        $wrongCatId = $ownCatId === 1 ? 4 : 1;
        $wrongCat = \App\Models\Category::find($wrongCatId);

        if (!$wrongCat) {
            $this->markTestSkipped("No category with id {$wrongCatId} in database.");
        }

        // A model redirect exists, but it's being requested under a category
        // the model does not belong to — must fall through to 404, not
        // redirect into a category page that isn't actually this model's.
        (new SlugRedirectResolver())->record('model', 'zz-old-model-wrong-cat-slug', $model->model_slug);

        $response = $this->get("/lagos/{$wrongCat->category_slug}/zz-old-model-wrong-cat-slug");

        $response->assertStatus(404);
    }
}
