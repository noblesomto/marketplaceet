<?php

namespace Tests\Feature\Phase7;

use Tests\TestCase;
use App\Models\Advert;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Services\FilterService;
use App\Services\AdvertQueryService;

/**
 * Phase 7 — Feature tests for routes wired to the new service layer.
 *
 * Validates that SearchFilter endpoints return well-formed JSON with the
 * expected keys and that AdvertController related/load-more routes work.
 * All tests are read-only (no database writes, no RefreshDatabase).
 */
class SearchFilterRoutesTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Filter AJAX endpoints — must return JSON {html, hasMore}
    // -------------------------------------------------------------------------

    public function test_filter_adverts_returns_json_with_expected_keys(): void
    {
        $response = $this->getJson('/filter/adverts');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore'])
                 ->assertJsonIsObject();
    }

    public function test_filter_adverts_with_category_returns_json(): void
    {
        $response = $this->getJson('/filter/adverts?category=2');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_filter_adverts_with_price_range_returns_json(): void
    {
        $response = $this->getJson('/filter/adverts?range=under_20k');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_filter_adverts_with_min_max_price_returns_json(): void
    {
        $response = $this->getJson('/filter/adverts?min=10000&max=500000');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);

        $data = $response->json();
        $this->assertIsBool($data['hasMore']);
    }

    public function test_filter_by_seller_returns_json(): void
    {
        $response = $this->getJson('/filter/sellers?sellers=all');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_filter_by_verified_seller_returns_json(): void
    {
        $response = $this->getJson('/filter/sellers?sellers=yes');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_filter_by_buydirect_returns_json(): void
    {
        $response = $this->getJson('/filter/buydirect?buydirect=Yes');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_filter_by_buydirect_without_param_returns_json(): void
    {
        // Previously the duplicate WHERE on null would cause an incorrect query.
        // After the bug fix, omitting the param must still return a valid response.
        $response = $this->getJson('/filter/buydirect');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_load_more_returns_json(): void
    {
        $response = $this->getJson('/adverts/load-more?page=1');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_load_more_with_search_term_returns_json(): void
    {
        $response = $this->getJson('/adverts/load-more?product=phone');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    // Car and phone detail filters are POST routes
    public function test_filter_by_car_details_returns_json(): void
    {
        $response = $this->postJson('/filter/car-details', []);

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_filter_by_car_details_with_condition_returns_json(): void
    {
        $response = $this->postJson('/filter/car-details', [
            'condition'    => ['Foreign Used'],
            'sub_category' => 2,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_filter_by_phone_details_returns_json(): void
    {
        $response = $this->postJson('/filter/phone-details', []);

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    public function test_filter_by_phone_details_with_condition_returns_json(): void
    {
        $response = $this->postJson('/filter/phone-details', [
            'condition'    => ['New'],
            'sub_category' => 6,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    // -------------------------------------------------------------------------
    // Search route
    // -------------------------------------------------------------------------

    public function test_search_page_returns_200(): void
    {
        $response = $this->get('/search?product=phone');

        $response->assertStatus(200)
                 ->assertViewIs('public.adverts');
    }

    public function test_search_page_with_category_filter_returns_200(): void
    {
        $response = $this->get('/search?category=2');

        $response->assertStatus(200)
                 ->assertViewIs('public.adverts');
    }

    // -------------------------------------------------------------------------
    // AdvertController — related ads (uses AdvertQueryService)
    // -------------------------------------------------------------------------

    public function test_related_ads_page_returns_200(): void
    {
        $ad = Advert::activeNotRecentlySold()->first();

        if (!$ad) {
            $this->markTestSkipped('No active adverts in database.');
        }

        $response = $this->get('/related/' . $ad->ad_id);

        $response->assertStatus(200)
                 ->assertViewIs('public.related');
    }

    public function test_related_ads_load_more_returns_json(): void
    {
        $ad = Advert::activeNotRecentlySold()->first();

        if (!$ad) {
            $this->markTestSkipped('No active adverts in database.');
        }

        $response = $this->getJson('/related/' . $ad->ad_id . '/load-more?page=2');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html', 'hasMore']);
    }

    // -------------------------------------------------------------------------
    // AdvertController — category pages (use AdvertQueryService::getBrandsForSubcat)
    // -------------------------------------------------------------------------

    public function test_subcategory_page_returns_200(): void
    {
        $ad = Advert::activeNotRecentlySold()
            ->whereNotNull('sub_category')
            ->whereNotNull('category')
            ->first();

        if (!$ad) {
            $this->markTestSkipped('No advert with category + subcategory found.');
        }

        $cat    = Category::find($ad->category);
        $subcat = SubCategory::find($ad->sub_category);

        if (!$cat || !$subcat) {
            $this->markTestSkipped('Category or SubCategory record missing.');
        }

        $url      = '/category/' . $cat->category_slug . '/' . $subcat->sub_cat_slug;
        $response = $this->get($url);

        $response->assertStatus(200);
    }

    public function test_brand_page_returns_200(): void
    {
        $ad = Advert::activeNotRecentlySold()
            ->whereNotNull('brand')
            ->whereNotNull('sub_category')
            ->whereNotNull('category')
            ->first();

        if (!$ad) {
            $this->markTestSkipped('No advert with category/subcat/brand found.');
        }

        $cat    = Category::find($ad->category);
        $subcat = SubCategory::find($ad->sub_category);
        $brand  = Brands::find($ad->brand);

        if (!$cat || !$subcat || !$brand) {
            $this->markTestSkipped('Category, SubCategory or Brand record missing.');
        }

        $url = '/category/'
            . $cat->category_slug . '/'
            . $subcat->sub_cat_slug . '/'
            . $brand->brand_slug;

        $response = $this->get($url);

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Service container — verify DI wiring is intact
    // -------------------------------------------------------------------------

    public function test_filter_service_resolves_from_container(): void
    {
        $service = $this->app->make(FilterService::class);

        $this->assertInstanceOf(FilterService::class, $service);
    }

    public function test_advert_query_service_resolves_from_container(): void
    {
        $service = $this->app->make(AdvertQueryService::class);

        $this->assertInstanceOf(AdvertQueryService::class, $service);
    }

    public function test_search_filter_controller_injects_services(): void
    {
        $controller = $this->app->make(\App\Http\Controllers\Shop\SearchFilter::class);

        $this->assertInstanceOf(\App\Http\Controllers\Shop\SearchFilter::class, $controller);
    }

    public function test_advert_controller_injects_services(): void
    {
        $controller = $this->app->make(\App\Http\Controllers\User\AdvertController::class);

        $this->assertInstanceOf(\App\Http\Controllers\User\AdvertController::class, $controller);
    }

    public function test_account_controller_injects_cookie_service(): void
    {
        $controller = $this->app->make(\App\Http\Controllers\Auth\AccountController::class);

        $this->assertInstanceOf(\App\Http\Controllers\Auth\AccountController::class, $controller);
    }
}
