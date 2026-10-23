<?php

namespace App\Services;

use App\Models\Advert;
use App\Models\State;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * AdvertQueryService
 *
 * Shared query logic extracted from AdvertController and SearchFilter:
 *   - buildRelatedQuery()  — 4-level fallback for "related ads"
 *   - getBrandsForSubcat() — brand aggregation JOIN used across 5 controller methods
 */
class AdvertQueryService
{
    /**
     * Build the related-ads query with progressive fallback.
     *
     * Levels (returns the first that has results):
     *   1 — same category + subcategory + brand
     *   2 — same category + subcategory
     *   3 — same category
     *   4 — any active ads (last resort)
     *
     * Within the chosen level, ads whose title contains $keyword are ranked first.
     */
    public function buildRelatedQuery(Advert $ad, string $keyword)
    {
        $titleOrder = ['CASE WHEN ad_title LIKE ? THEN 1 ELSE 2 END ASC, RAND()', ['%' . $keyword . '%']];

        $base = fn() => Advert::with('firstImage', 'owner')
            ->where('id', '!=', $ad->id)
            ->activeNotRecentlySold();

        // Level 1: category + sub_category + brand
        if ($ad->brand) {
            $q = $base()
                ->where('category', $ad->category)
                ->where('sub_category', $ad->sub_category)
                ->where('brand', $ad->brand);

            if ((clone $q)->count() > 0) {
                return $q->orderByRaw(...$titleOrder);
            }
        }

        // Level 2: category + sub_category
        if ($ad->sub_category) {
            $q = $base()
                ->where('category', $ad->category)
                ->where('sub_category', $ad->sub_category);

            if ((clone $q)->count() > 0) {
                return $q->orderByRaw(...$titleOrder);
            }
        }

        // Level 3: category only
        $q = $base()->where('category', $ad->category);

        if ((clone $q)->count() > 0) {
            return $q->orderByRaw(...$titleOrder);
        }

        // Level 4: any active ads (last resort)
        return $base()->inRandomOrder();
    }

    /**
     * Get brands with active advert counts for a given subcategory.
     *
     * Used in: sub_category(), brand(), all_subcat() (AdvertController)
     *          location_subcat(), location_brand() (SearchFilter)
     */
    public function getBrandsForSubcat(int $subcatId): Collection
    {
        return DB::table('brands')
            ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
            ->where('brands.subcat_id', $subcatId)
            ->where(function ($query) {
                $query->where('adverts.ad_status', 1)
                      ->where(function ($q) {
                          $q->where('adverts.sold_date', '>=', now()->subDays(30))
                            ->orWhereNull('adverts.sold_date');
                      });
            })
            ->select(
                'brands.id',
                'brands.brand',
                'brands.brand_slug',
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
            ->orderBy('advert_count', 'desc')
            ->get();
    }

    /**
     * Get brands with active advert counts for a given category (across all
     * of its subcategories), optionally scoped to a single location.
     *
     * Used in: category.blade.php / main-category.blade.php "Shop by Brand"
     * sidebar, to link into the location+category+brand pages.
     */
    public function getBrandsForCategory(int $categoryId, ?string $stateSlug = null): Collection
    {
        return DB::table('brands')
            ->join('sub_categories', 'brands.subcat_id', '=', 'sub_categories.id')
            ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
            ->where('sub_categories.cat_id', $categoryId)
            ->where(function ($query) use ($stateSlug) {
                $query->where('adverts.ad_status', 1)
                      ->where(function ($q) {
                          $q->where('adverts.sold_date', '>=', now()->subDays(30))
                            ->orWhereNull('adverts.sold_date');
                      });

                if ($stateSlug) {
                    // A {location} URL segment can be a state-level slug
                    // (adverts.state, e.g. "lagos") or an LGA-level slug
                    // (adverts.state_slug, e.g. "ikeja") — check both. Multi-word
                    // states ("Akwa Ibom") can't match their own hyphenated slug
                    // as a plain string, so also resolve it via the states table.
                    $stateName = State::nameForSlug($stateSlug);
                    $query->where(function ($q) use ($stateSlug, $stateName) {
                        $q->where('adverts.state', $stateSlug)->orWhere('adverts.state_slug', $stateSlug);

                        if ($stateName) {
                            $q->orWhere('adverts.state', $stateName);
                        }
                    });
                }
            })
            ->select(
                'brands.id',
                'brands.brand',
                'brands.brand_slug',
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
            ->orderBy('advert_count', 'desc')
            ->get();
    }

    /**
     * Get models with active advert counts for a given brand, scoped to a
     * category and optionally a single location. Only categories with a
     * model-bearing detail table (vehicles → car_details, mobile phones →
     * phone_details) are supported; any other category returns empty.
     *
     * Used in: brand.blade.php "Shop by Model" sidebar.
     */
    public function getModelsForBrand(int $brandId, int $categoryId, ?string $stateSlug = null): Collection
    {
        $detailTable = match ($categoryId) {
            1 => 'car_details',
            4 => 'phone_details',
            default => null,
        };

        if (!$detailTable) {
            return collect();
        }

        return DB::table('models')
            ->leftJoin($detailTable, 'models.id', '=', "{$detailTable}.model")
            ->leftJoin('adverts', "{$detailTable}.advert_id", '=', 'adverts.id')
            ->where('models.brand_id', $brandId)
            ->where(function ($query) use ($stateSlug) {
                $query->where('adverts.ad_status', 1)
                      ->where(function ($q) {
                          $q->where('adverts.sold_date', '>=', now()->subDays(30))
                            ->orWhereNull('adverts.sold_date');
                      });

                if ($stateSlug) {
                    $stateName = State::nameForSlug($stateSlug);
                    $query->where(function ($q) use ($stateSlug, $stateName) {
                        $q->where('adverts.state', $stateSlug)->orWhere('adverts.state_slug', $stateSlug);

                        if ($stateName) {
                            $q->orWhere('adverts.state', $stateName);
                        }
                    });
                }
            })
            ->select(
                'models.id',
                'models.model',
                'models.model_slug',
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('models.id', 'models.model', 'models.model_slug')
            ->orderBy('advert_count', 'desc')
            ->get();
    }
}
