<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Advert Statistics
 *
 * APIs for retrieving advertisement statistics, counts, and filtering
 */
class AdvertStatsController extends Controller
{
    /**
     * Get adverts count with filters
     *
     * Returns the total count of active adverts based on various filters.
     * All filters are optional and can be combined.
     *
     * @authenticated
     *
     * @queryParam category integer The category ID. Example: 1
     * @queryParam sub_category integer The sub-category ID. Example: 5
     * @queryParam brand integer The brand ID. Example: 10
     * @queryParam buy_direct string Filter by buy direct option. Use "Yes" or "No". Example: Yes
     * @queryParam state string The state name. Example: Lagos
     * @queryParam from_date date Filter adverts from this date (YYYY-MM-DD). Example: 2025-01-01
     * @queryParam to_date date Filter adverts to this date (YYYY-MM-DD). Example: 2025-01-31
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "total_count": 1500,
     *     "filters_applied": {
     *       "category": "1",
     *       "state": "Lagos"
     *     }
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to fetch advert count",
     *   "error": "Error details"
     * }
     */
    public function getAdvertCount(Request $request)
    {
        try {
            $filters = $request->only([
                'category',
                'sub_category',
                'brand',
                'buy_direct',
                'state',
                'from_date',
                'to_date'
            ]);

            $count = getAdvertCount($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_count' => $count,
                    'filters_applied' => $filters
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch advert count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get adverts grouped by state
     *
     * Returns the count of active adverts grouped by Ethiopian regions.
     * Results are ordered by state with the most adverts first.
     *
     * @authenticated
     *
     * @queryParam category integer Filter by category ID. Example: 1
     * @queryParam sub_category integer Filter by sub-category ID. Example: 5
     * @queryParam brand integer Filter by brand ID. Example: 10
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "states": [
     *       {
     *         "state": "Lagos",
     *         "total": 500
     *       },
     *       {
     *         "state": "Abuja",
     *         "total": 300
     *       },
     *       {
     *         "state": "Port Harcourt",
     *         "total": 250
     *       }
     *     ],
     *     "filters_applied": {
     *       "category": "1"
     *     }
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to fetch adverts by state",
     *   "error": "Error details"
     * }
     */
    public function getAdvertsByState(Request $request)
    {
        try {
            $filters = $request->only(['category', 'sub_category', 'brand']);

            $stateData = getAdvertsGroupedByState($filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'states' => $stateData,
                    'filters_applied' => $filters
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch adverts by state',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get advert count filtered by verified sellers
     *
     * Returns the count of adverts from verified or unverified sellers.
     * Useful for showing marketplace trust metrics.
     *
     * @authenticated
     *
     * @queryParam verified string Filter by seller verification status. Use "yes" or "no". Defaults to "yes". Example: yes
     * @queryParam category string|integer Category ID or slug. Example: electronics
     * @queryParam sub_category string|integer Sub-category ID or slug. Example: mobile-phones
     * @queryParam brand string|integer Brand ID or slug. Example: samsung
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "count": 250,
     *     "verified": "yes",
     *     "category": "electronics",
     *     "sub_category": "mobile-phones",
     *     "brand": "samsung"
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to fetch filtered advert count",
     *   "error": "Error details"
     * }
     */
    public function getAdvertCountByFilter(Request $request)
    {
        try {
            $verified = $request->input('verified', 'yes');
            $category = $request->input('category');
            $subCategory = $request->input('sub_category');
            $brand = $request->input('brand');

            $count = advert_count_by_filter($verified, $category, $subCategory, $brand);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                    'verified' => $verified,
                    'category' => $category,
                    'sub_category' => $subCategory,
                    'brand' => $brand
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch filtered advert count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get brands with advert counts
     *
     * Returns a list of brands with their active advert counts.
     * Can be filtered by category or sub-category. Only returns brands with at least one active advert.
     *
     * @authenticated
     *
     * @queryParam category_id integer Filter brands by category ID. Example: 1
     * @queryParam sub_category_id integer Filter brands by sub-category ID. Example: 5
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "brands": [
     *       {
     *         "id": 1,
     *         "brand_name": "Samsung",
     *         "brand_slug": "samsung",
     *         "adverts_count": 45,
     *         "sub_category": {
     *           "id": 5,
     *           "sub_cat_name": "Mobile Phones",
     *           "category": {
     *             "id": 1,
     *             "category_name": "Electronics"
     *           }
     *         }
     *       },
     *       {
     *         "id": 2,
     *         "brand_name": "Apple",
     *         "brand_slug": "apple",
     *         "adverts_count": 38,
     *         "sub_category": {
     *           "id": 5,
     *           "sub_cat_name": "Mobile Phones",
     *           "category": {
     *             "id": 1,
     *             "category_name": "Electronics"
     *           }
     *         }
     *       }
     *     ],
     *     "category_id": 1,
     *     "sub_category_id": 5
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to fetch brands with advert count",
     *   "error": "Error details"
     * }
     */
    public function getBrandsWithAdvertCount(Request $request)
    {
        try {
            $categoryId = $request->input('category_id');
            $subCategoryId = $request->input('sub_category_id');

            $brands = get_brands_with_advert_count($categoryId, $subCategoryId);

            return response()->json([
                'success' => true,
                'data' => [
                    'brands' => $brands,
                    'category_id' => $categoryId,
                    'sub_category_id' => $subCategoryId
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch brands with advert count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
