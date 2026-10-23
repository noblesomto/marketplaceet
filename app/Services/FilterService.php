<?php

namespace App\Services;

use App\Models\Advert;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

/**
 * FilterService - Centralized filtering logic for adverts
 *
 * Extracts common filtering patterns from AdvertController and SearchFilter
 * to reduce code duplication and improve maintainability.
 */
class FilterService
{
    /**
     * Apply base filters (category, subcategory, brand, location)
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyContextFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        return $query;
    }

    /**
     * Apply price filters (min, max, range)
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyPriceFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('min')) {
            $query->where('price', '>=', (int) $request->input('min'));
        }

        if ($request->filled('max')) {
            $query->where('price', '<=', (int) $request->input('max'));
        }

        if ($request->filled('range')) {
            $this->applyPriceRange($query, $request->input('range'));
        }

        return $query;
    }

    /**
     * Apply predefined price ranges
     *
     * @param Builder $query
     * @param string $range
     * @return Builder
     */
    public function applyPriceRange(Builder $query, string $range): Builder
    {
        // Bucket boundaries are ETB-denominated (slugs kept as-is for API/URL compatibility).
        switch ($range) {
            case 'under_20k':
                $query->where('price', '<', 2000);
                break;
            case '20k_120k':
                $query->whereBetween('price', [2000, 10000]);
                break;
            case '120k_1m':
                $query->whereBetween('price', [10000, 100000]);
                break;
            case '1m_10m':
                $query->whereBetween('price', [100000, 1000000]);
                break;
            case 'above_10m':
                $query->where('price', '>', 1000000);
                break;
        }

        return $query;
    }

    /**
     * Apply seller filter
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applySellerFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('seller')) {
            $sellerType = $request->seller;

            if ($sellerType === 'verified') {
                $query->whereHas('user', function ($q) {
                    $q->where('verified', 'yes');
                });
            } elseif ($sellerType === 'unverified') {
                $query->whereHas('user', function ($q) {
                    $q->where('verified', 'no');
                });
            }
        }

        return $query;
    }

    /**
     * Apply buy direct filter
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyBuyDirectFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('buydirect')) {
            $buyDirect = $request->buydirect;

            if ($buyDirect === 'yes') {
                $query->where('buy_direct', 'Yes');
            } elseif ($buyDirect === 'no') {
                $query->where('buy_direct', 'No');
            }
        }

        return $query;
    }

    /**
     * Apply condition filter, routing to the correct table based on category.
     *
     * - Category 1 (Vehicles)              → car_details.condition
     * - Category 4 (Mobile Phone & Tablets) → phone_details.condition
     * - All others                          → adverts.item_condition
     */
    public function applyConditionFilter(Builder $query, Request $request): Builder
    {
        if (!$request->filled('item_condition')) {
            return $query;
        }

        $condition = $request->input('item_condition');
        $category  = (int) $request->input('category');

        if ($category === 1) {
            $query->whereHas('car', fn($q) => $q->where('condition', $condition));
        } elseif ($category === 4) {
            $query->whereHas('phone', fn($q) => $q->where('condition', $condition));
        } else {
            $query->where('item_condition', $condition);
        }

        return $query;
    }

    /**
     * Apply car detail filters (condition, fuel type, transmission, registration)
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyCarFilters(Builder $query, Request $request): Builder
    {
        $query->whereHas('car', function ($q) use ($request) {
            if ($request->filled('condition')) {
                $q->where('condition', $request->condition);
            }

            if ($request->filled('fuel_type')) {
                $q->where('fuel_type', $request->fuel_type);
            }

            if ($request->filled('transmission')) {
                $q->where('transmission', $request->transmission);
            }

            if ($request->filled('registration')) {
                $q->where('registration', $request->registration);
            }
        });

        return $query;
    }

    /**
     * Apply phone detail filters (condition, device type)
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyPhoneFilters(Builder $query, Request $request): Builder
    {
        $query->whereHas('phone', function ($q) use ($request) {
            if ($request->filled('condition')) {
                $q->where('condition', $request->condition);
            }

            if ($request->filled('device_type')) {
                $q->where('device_type', $request->device_type);
            }
        });

        return $query;
    }

    /**
     * Apply all standard filters at once
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyAllFilters(Builder $query, Request $request): Builder
    {
        $this->applyContextFilters($query, $request);
        $this->applyPriceFilters($query, $request);
        $this->applySellerFilter($query, $request);
        $this->applyBuyDirectFilter($query, $request);
        $this->applyConditionFilter($query, $request);

        return $query;
    }

    /**
     * Get filter parameters from request
     *
     * @param Request $request
     * @return array
     */
    public function getFilterParams(Request $request): array
    {
        return [
            'category' => $request->input('category'),
            'sub_category' => $request->input('sub_category'),
            'brand' => $request->input('brand'),
            'location' => $request->input('location'),
            'min' => $request->input('min'),
            'max' => $request->input('max'),
            'range' => $request->input('range'),
            'seller' => $request->input('seller'),
            'buydirect' => $request->input('buydirect'),
        ];
    }

    /**
     * Render advert cards into an HTML string for AJAX responses.
     *
     * Used in all load-more and filter AJAX endpoints to avoid repeating
     * the mobile/desktop branch 6+ times across SearchFilter and AdvertController.
     *
     * @param iterable    $adverts
     * @param bool|null   $isMobile  null = auto-detect via Agent
     * @return string
     */
    public function renderAdvertCards($adverts, ?bool $isMobile = null): string
    {
        if ($isMobile === null) {
            $agent    = new Agent();
            $isMobile = $agent->isMobile();
        }

        $mobileView  = 'public.components.advert.advert-card-mobile';
        $desktopView = 'public.components.advert.advert-card';
        $html = '';

        foreach ($adverts as $row) {
            $html .= view($isMobile ? $mobileView : $desktopView, compact('row'))->render();
        }

        return $html;
    }

    /**
     * Build a product-detail filter query joined to a detail table.
     *
     * Shared core for car and phone detail filtering — the only differences
     * are the join table name and the detail-specific filter columns.
     *
     * @param Request  $request
     * @param string   $detailTable      e.g. 'car_details' or 'phone_details'
     * @param callable $applyDetailFilters  closure(Builder $query, Request $request)
     * @return Builder
     */
    private function buildDetailQuery(Request $request, string $detailTable, callable $applyDetailFilters): Builder
    {
        $query = Advert::with('firstImage')
            ->join($detailTable, 'adverts.id', '=', $detailTable . '.advert_id')
            ->where('adverts.ad_status', 'active')
            ->where('adverts.sold', 'No')
            ->select('adverts.*');

        // Detail-specific filters (condition, transmission, device_type, etc.)
        $applyDetailFilters($query, $request);

        // Context filters (prefixed because of the JOIN)
        if ($request->filled('category')) {
            $query->where('adverts.category', $request->category);
        }
        if ($request->filled('sub_category')) {
            $query->where('adverts.sub_category', $request->sub_category);
        }
        if ($request->filled('brand')) {
            $query->where('adverts.brand', $request->brand);
        }
        if ($request->filled('location')) {
            $query->where('adverts.state', $request->location);
        }

        // Price filters
        if ($request->filled('min')) {
            $query->where('adverts.price', '>=', (int) $request->min);
        }
        if ($request->filled('max')) {
            $query->where('adverts.price', '<=', (int) $request->max);
        }

        // Buy direct filter
        if ($request->filled('buydirect')) {
            $query->where('adverts.buy_direct', $request->buydirect);
        }

        // Verified seller filter
        $sellers = $request->input('sellers', 'all');
        if ($sellers !== 'all') {
            $query->whereHas('owner', function ($q) use ($sellers) {
                $q->where('verified', $sellers);
            });
        }

        return $query
            ->orderBy('adverts.featured', 'DESC')
            ->orderBy('adverts.created_at', 'DESC');
    }

    /**
     * Build a car-detail filter query (condition, registration, fuel, transmission).
     */
    public function buildCarDetailQuery(Request $request): Builder
    {
        return $this->buildDetailQuery($request, 'car_details', function (Builder $query, Request $request) {
            if ($request->filled('condition') && !empty($request->condition)) {
                $query->whereIn('car_details.condition', (array) $request->condition);
            }
            if ($request->filled('registration') && !empty($request->registration)) {
                $query->whereIn('car_details.registration', (array) $request->registration);
            }
            if ($request->filled('fuel_type') && !empty($request->fuel_type)) {
                $query->whereIn('car_details.fuel', (array) $request->fuel_type);
            }
            if ($request->filled('transmission') && !empty($request->transmission)) {
                $query->whereIn('car_details.transmission', (array) $request->transmission);
            }
        });
    }

    /**
     * Build a phone-detail filter query (condition, device type).
     */
    public function buildPhoneDetailQuery(Request $request): Builder
    {
        return $this->buildDetailQuery($request, 'phone_details', function (Builder $query, Request $request) {
            if ($request->filled('condition') && !empty($request->condition)) {
                $query->whereIn('phone_details.condition', (array) $request->condition);
            }
            if ($request->filled('device_type') && !empty($request->device_type)) {
                $query->whereIn('phone_details.device', (array) $request->device_type);
            }
        });
    }
}
