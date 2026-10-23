<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AdvertQueryService;
use App\Models\Advert;
use App\Models\SubCategory;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class AdvertQueryServiceTest extends TestCase
{
    private AdvertQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdvertQueryService();
    }

    // -------------------------------------------------------------------------
    // getBrandsForSubcat
    // -------------------------------------------------------------------------

    public function test_get_brands_for_subcat_returns_collection(): void
    {
        $subcat = SubCategory::first();

        if (!$subcat) {
            $this->markTestSkipped('No subcategories in database.');
        }

        $result = $this->service->getBrandsForSubcat($subcat->id);

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_get_brands_for_subcat_returns_correct_columns(): void
    {
        $subcat = SubCategory::first();

        if (!$subcat) {
            $this->markTestSkipped('No subcategories in database.');
        }

        $result = $this->service->getBrandsForSubcat($subcat->id);

        if ($result->isEmpty()) {
            $this->markTestSkipped('No brands with active adverts for first subcategory.');
        }

        $first = $result->first();
        $this->assertObjectHasProperty('id', $first);
        $this->assertObjectHasProperty('brand', $first);
        $this->assertObjectHasProperty('brand_slug', $first);
        $this->assertObjectHasProperty('advert_count', $first);
    }

    public function test_get_brands_for_subcat_ordered_by_advert_count_desc(): void
    {
        $subcat = SubCategory::first();

        if (!$subcat) {
            $this->markTestSkipped('No subcategories in database.');
        }

        $result = $this->service->getBrandsForSubcat($subcat->id);

        if ($result->count() < 2) {
            $this->markTestSkipped('Need at least 2 brands to test ordering.');
        }

        $counts = $result->pluck('advert_count')->toArray();
        $sorted = $counts;
        rsort($sorted);

        $this->assertSame($sorted, $counts, 'Brands should be ordered by advert_count descending.');
    }

    public function test_get_brands_for_nonexistent_subcat_returns_empty_collection(): void
    {
        $result = $this->service->getBrandsForSubcat(999999);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    // -------------------------------------------------------------------------
    // buildRelatedQuery
    // -------------------------------------------------------------------------

    public function test_build_related_query_returns_builder(): void
    {
        $ad = Advert::activeNotRecentlySold()->first();

        if (!$ad) {
            $this->markTestSkipped('No active adverts in database.');
        }

        $result = $this->service->buildRelatedQuery($ad, 'test');

        $this->assertInstanceOf(Builder::class, $result);
    }

    public function test_build_related_query_excludes_source_ad(): void
    {
        $ad = Advert::activeNotRecentlySold()->first();

        if (!$ad) {
            $this->markTestSkipped('No active adverts in database.');
        }

        $keyword = implode(' ', array_slice(explode(' ', $ad->ad_title), 0, 2));
        $query   = $this->service->buildRelatedQuery($ad, $keyword);
        $ids     = $query->pluck('id')->toArray();

        $this->assertNotContains(
            $ad->id,
            $ids,
            'Related query must not include the source ad itself.'
        );
    }

    public function test_build_related_query_returns_only_active_ads(): void
    {
        $ad = Advert::activeNotRecentlySold()->first();

        if (!$ad) {
            $this->markTestSkipped('No active adverts in database.');
        }

        $query  = $this->service->buildRelatedQuery($ad, 'test');
        $result = $query->limit(20)->get();

        foreach ($result as $relatedAd) {
            $this->assertEquals('active', $relatedAd->ad_status,
                "Related ad #{$relatedAd->id} must have ad_status = active."
            );
        }
    }
}
