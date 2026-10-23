<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FilterService;
use App\Models\Advert;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FilterServiceTest extends TestCase
{
    private FilterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FilterService();
    }

    // -------------------------------------------------------------------------
    // renderAdvertCards
    // -------------------------------------------------------------------------

    public function test_render_advert_cards_returns_empty_string_for_empty_collection(): void
    {
        $html = $this->service->renderAdvertCards(collect(), false);

        $this->assertIsString($html);
        $this->assertSame('', $html);
    }

    public function test_render_advert_cards_accepts_null_mobile_param(): void
    {
        // Should not throw — Agent auto-detects (CLI = not mobile)
        $html = $this->service->renderAdvertCards(collect(), null);

        $this->assertIsString($html);
    }

    public function test_render_advert_cards_renders_desktop_cards(): void
    {
        $ads = Advert::with('firstImage')->activeNotRecentlySold()->limit(2)->get();

        if ($ads->isEmpty()) {
            $this->markTestSkipped('No active adverts in database.');
        }

        $html = $this->service->renderAdvertCards($ads, false);

        $this->assertIsString($html);
        $this->assertNotEmpty($html);
    }

    public function test_render_advert_cards_renders_mobile_cards(): void
    {
        $ads = Advert::with('firstImage')->activeNotRecentlySold()->limit(2)->get();

        if ($ads->isEmpty()) {
            $this->markTestSkipped('No active adverts in database.');
        }

        $html = $this->service->renderAdvertCards($ads, true);

        $this->assertIsString($html);
        $this->assertNotEmpty($html);
    }

    public function test_render_advert_cards_desktop_and_mobile_output_differ(): void
    {
        $ads = Advert::with('firstImage')->activeNotRecentlySold()->limit(1)->get();

        if ($ads->isEmpty()) {
            $this->markTestSkipped('No active adverts in database.');
        }

        $desktop = $this->service->renderAdvertCards($ads, false);
        $mobile  = $this->service->renderAdvertCards($ads, true);

        // They use different view files so the HTML will be different
        $this->assertNotEquals($desktop, $mobile,
            'Desktop and mobile card views should produce different HTML.'
        );
    }

    // -------------------------------------------------------------------------
    // applyContextFilters
    // -------------------------------------------------------------------------

    public function test_apply_context_filters_adds_category_where_clause(): void
    {
        $request = Request::create('/', 'GET', ['category' => 5]);
        $query   = Advert::query();

        $this->service->applyContextFilters($query, $request);

        $sql = $query->toSql();
        $this->assertStringContainsString('category', $sql);
    }

    public function test_apply_context_filters_skips_empty_params(): void
    {
        $request = Request::create('/', 'GET', []);
        $query   = Advert::query();
        $baseSql = $query->toSql();

        $this->service->applyContextFilters($query, $request);

        // SQL unchanged when no filter params provided
        $this->assertSame($baseSql, $query->toSql());
    }

    public function test_apply_context_filters_applies_all_four_params(): void
    {
        $request = Request::create('/', 'GET', [
            'category'     => 1,
            'sub_category' => 2,
            'brand'        => 3,
            'location'     => 'Addis Ababa',
        ]);
        $query = Advert::query();

        $this->service->applyContextFilters($query, $request);

        $sql = $query->toSql();
        $this->assertStringContainsString('category', $sql);
        $this->assertStringContainsString('sub_category', $sql);
        $this->assertStringContainsString('brand', $sql);
        $this->assertStringContainsString('state', $sql);
    }

    // -------------------------------------------------------------------------
    // applyPriceFilters
    // -------------------------------------------------------------------------

    public function test_apply_price_filters_adds_min_and_max(): void
    {
        $request = Request::create('/', 'GET', ['min' => 1000, 'max' => 50000]);
        $query   = Advert::query();

        $this->service->applyPriceFilters($query, $request);

        $sql = $query->toSql();
        $this->assertStringContainsString('price', $sql);
    }

    public function test_apply_price_filters_range_under_20k(): void
    {
        $request = Request::create('/', 'GET', ['range' => 'under_20k']);
        $query   = Advert::query();

        $this->service->applyPriceFilters($query, $request);

        $this->assertStringContainsString('price', $query->toSql());
    }

    public function test_apply_price_filters_skips_when_empty(): void
    {
        $request = Request::create('/', 'GET', []);
        $query   = Advert::query();
        $baseSql = $query->toSql();

        $this->service->applyPriceFilters($query, $request);

        $this->assertSame($baseSql, $query->toSql());
    }

    // -------------------------------------------------------------------------
    // buildCarDetailQuery / buildPhoneDetailQuery
    // -------------------------------------------------------------------------

    public function test_build_car_detail_query_returns_builder(): void
    {
        $request = Request::create('/', 'GET', []);
        $result  = $this->service->buildCarDetailQuery($request);

        $this->assertInstanceOf(Builder::class, $result);
    }

    public function test_build_car_detail_query_joins_car_details_table(): void
    {
        $request = Request::create('/', 'GET', []);
        $query   = $this->service->buildCarDetailQuery($request);

        $this->assertStringContainsString('car_details', $query->toSql());
    }

    public function test_build_phone_detail_query_returns_builder(): void
    {
        $request = Request::create('/', 'GET', []);
        $result  = $this->service->buildPhoneDetailQuery($request);

        $this->assertInstanceOf(Builder::class, $result);
    }

    public function test_build_phone_detail_query_joins_phone_details_table(): void
    {
        $request = Request::create('/', 'GET', []);
        $query   = $this->service->buildPhoneDetailQuery($request);

        $this->assertStringContainsString('phone_details', $query->toSql());
    }

    public function test_car_and_phone_detail_queries_join_different_tables(): void
    {
        $request = Request::create('/', 'GET', []);

        $carSql   = $this->service->buildCarDetailQuery($request)->toSql();
        $phoneSql = $this->service->buildPhoneDetailQuery($request)->toSql();

        $this->assertNotSame($carSql, $phoneSql,
            'Car and phone detail queries must join different tables.'
        );
        $this->assertStringContainsString('car_details', $carSql);
        $this->assertStringContainsString('phone_details', $phoneSql);
    }

    public function test_build_car_detail_query_applies_condition_filter(): void
    {
        $request = Request::create('/', 'GET', ['condition' => ['Foreign Used']]);
        $query   = $this->service->buildCarDetailQuery($request);

        $this->assertStringContainsString('condition', $query->toSql());
    }

    public function test_build_phone_detail_query_applies_device_type_filter(): void
    {
        $request = Request::create('/', 'GET', ['device_type' => ['Android']]);
        $query   = $this->service->buildPhoneDetailQuery($request);

        $this->assertStringContainsString('device', $query->toSql());
    }

    public function test_build_car_detail_query_applies_context_filters(): void
    {
        $request = Request::create('/', 'GET', [
            'category'     => 1,
            'sub_category' => 2,
            'brand'        => 3,
            'location'     => 'Addis Ababa',
            'min'          => 500000,
            'max'          => 5000000,
        ]);
        $sql = $this->service->buildCarDetailQuery($request)->toSql();

        $this->assertStringContainsString('category', $sql);
        $this->assertStringContainsString('sub_category', $sql);
        $this->assertStringContainsString('brand', $sql);
        $this->assertStringContainsString('state', $sql);
        $this->assertStringContainsString('price', $sql);
    }

    public function test_build_car_detail_query_executes_without_error(): void
    {
        $request = Request::create('/', 'GET', []);

        // Should not throw a DB or query exception
        $result = $this->service->buildCarDetailQuery($request)->limit(5)->get();
        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_build_phone_detail_query_executes_without_error(): void
    {
        $request = Request::create('/', 'GET', []);

        $result = $this->service->buildPhoneDetailQuery($request)->limit(5)->get();
        $this->assertInstanceOf(Collection::class, $result);
    }
}
