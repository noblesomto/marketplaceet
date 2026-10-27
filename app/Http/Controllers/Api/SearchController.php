<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Advert;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Brands;
use App\Models\VehicleModel;
use App\Services\AdvertQueryService;
use App\Models\State;
use Jenssegers\Agent\Agent;
use App\Services\FilterService;
use App\Support\ComputerSpecs;
use App\Support\PhoneSpecs;

/**
 * @group Search
 *
 * APIs for searching and filtering adverts across the platform
 */
class SearchController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/search",
     *     summary="Search adverts with filters",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="product", type="string", description="Product search term", example="iPhone 13"),
     *             @OA\Property(property="location", type="string", description="State/Location", example="Lagos"),
     *             @OA\Property(property="category", type="integer", description="Category ID", example=1),
     *             @OA\Property(property="sub_category", type="integer", description="Sub-category ID", example=6),
     *             @OA\Property(property="brand", type="integer", description="Brand ID", example=5),
     *             @OA\Property(property="buydirect", type="string", description="Buy direct filter", example="yes"),
     *             @OA\Property(property="per_page", type="integer", description="Items per page", example=10),
     *             @OA\Property(property="page", type="integer", description="Page number", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results with pagination",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object"),
     *             @OA\Property(property="search_params", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product'        => 'nullable|string|min:3',
            'location'       => 'nullable',
            'category'       => 'nullable',
            'sub_category'   => 'nullable',
            'brand'          => 'nullable',
            'buydirect'      => 'nullable',
            'item_condition' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $filterService = new FilterService();
        $query = Advert::with('firstImage')->activeNotRecentlySold();

        if ($request->filled('product')) {
            $query->where(function ($q) use ($request) {
                $q->where('ad_title', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('title_slug', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('ad_id', $request->product);
            });
        }

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

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        $filterService->applyConditionFilter($query, $request);

        $ads = $query->orderWithFeatured()
                    ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ],
            'search_params' => [
                'product'        => $request->input('product'),
                'location'       => $request->input('location'),
                'category'       => $request->input('category'),
                'sub_category'   => $request->input('sub_category'),
                'brand'          => $request->input('brand'),
                'buydirect'      => $request->input('buydirect'),
                'item_condition' => $request->input('item_condition'),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/search/filter",
     *     summary="Unified advert filter — replaces all previous filter-by-* endpoints",
     *     description="All parameters are optional. Pass only the ones you need. Car-specific params (car_condition, fuel_type, transmission, registration) are applied via a JOIN on car_details. Phone-specific params (phone_condition, device_type, phone_ram, phone_storage) are applied via a JOIN on phone_details. Laptop params (processor, ram, storage) are applied via computer_details — values as in GET /api/adverts/create/data → computer_options.",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="keyword",        type="string",  description="Search term — matches ad title or ad_id", example="iPhone 13"),
     *             @OA\Property(property="category",       type="integer", description="Category ID", example=1),
     *             @OA\Property(property="sub_category",   type="integer", description="Sub-category ID", example=6),
     *             @OA\Property(property="brand",          type="integer", description="Brand ID", example=5),
     *             @OA\Property(property="location",       type="string",  description="State name", example="Lagos"),
     *             @OA\Property(property="min",            type="integer", description="Minimum price", example=10000),
     *             @OA\Property(property="max",            type="integer", description="Maximum price", example=500000),
     *             @OA\Property(property="range",          type="string",  description="Price range shortcut", enum={"under_20k","20k_120k","120k_1m","1m_10m","above_10m"}),
     *             @OA\Property(property="buydirect",      type="string",  description="Buy Direct filter", enum={"Yes","No"}),
     *             @OA\Property(property="seller",         type="string",  description="Seller verification", enum={"all","verified","unverified"}),
     *             @OA\Property(property="item_condition", type="string",  description="General item condition (non-car, non-phone)", example="New"),
     *             @OA\Property(property="car_condition",  type="string",  description="Vehicle condition — single value or array", example="Foreign used"),
     *             @OA\Property(property="fuel_type",      type="string",  description="Vehicle fuel type — single value or array", example="Petrol"),
     *             @OA\Property(property="transmission",   type="string",  description="Vehicle transmission — single value or array", example="Automatic"),
     *             @OA\Property(property="registration",   type="string",  description="Vehicle registration status — single value or array", example="Registered"),
     *             @OA\Property(property="phone_condition",type="string",  description="Phone condition — single value or array", example="New - Unboxed"),
     *             @OA\Property(property="device_type",    type="string",  description="Phone device type — single value or array", example="Smartphone"),
     *             @OA\Property(property="phone_ram",      type="string",  description="Phone RAM — single value or array (values from create/data → phone_spec_options.ram)", example="8GB"),
     *             @OA\Property(property="phone_storage",  type="string",  description="Phone storage — single value or array (values from create/data → phone_spec_options.storage)", example="128GB"),
     *             @OA\Property(property="processor",      type="string",  description="Laptop processor — single value or array", example="Intel Core i5"),
     *             @OA\Property(property="ram",            type="string",  description="Laptop RAM — single value or array", example="8GB"),
     *             @OA\Property(property="storage",        type="string",  description="Laptop storage size — single value or array", example="256GB"),
     *             @OA\Property(property="per_page",       type="integer", description="Results per page (default 20)", example=20),
     *             @OA\Property(property="page",           type="integer", description="Page number", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Filtered results with active filters echoed back")
     * )
     */
    public function filter(Request $request)
    {
        $filterService = new FilterService();

        $hasCarFilters   = $request->hasAny(['car_condition', 'fuel_type', 'transmission', 'registration']);
        $hasPhoneFilters = $request->hasAny(['phone_condition', 'device_type', 'phone_ram', 'phone_storage']);
        $hasComputerFilters = $request->hasAny(['processor', 'ram', 'storage']);

        $with = ['firstImage'];
        if ($hasCarFilters)   $with[] = 'car';
        if ($hasPhoneFilters) $with[] = 'phone';
        if ($hasComputerFilters) $with[] = 'computer';

        $query = Advert::with($with)->activeNotRecentlySold();

        // Keyword / ad_id search
        $keyword = $request->input('keyword') ?? $request->input('product');
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('ad_title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('title_slug', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('ad_id', $keyword);
            });
        }

        // Standard filters (category, sub_category, brand, location, price, seller, buydirect, item_condition)
        $filterService->applyContextFilters($query, $request);
        $filterService->applyPriceFilters($query, $request);
        $filterService->applySellerFilter($query, $request);
        $filterService->applyBuyDirectFilter($query, $request);
        $filterService->applyConditionFilter($query, $request);

        // Car-specific filters — only run when at least one car param is present
        if ($hasCarFilters) {
            $query->whereHas('car', function ($q) use ($request) {
                if ($request->filled('car_condition')) {
                    $q->whereIn('condition', (array) $request->car_condition);
                }
                if ($request->filled('fuel_type')) {
                    $q->whereIn('fuel', (array) $request->fuel_type);
                }
                if ($request->filled('transmission')) {
                    $q->whereIn('transmission', (array) $request->transmission);
                }
                if ($request->filled('registration')) {
                    $q->whereIn('registration', (array) $request->registration);
                }
            });
        }

        // Phone-specific filters — only run when at least one phone param is present
        if ($hasPhoneFilters) {
            $query->whereHas('phone', function ($q) use ($request) {
                if ($request->filled('phone_condition')) {
                    $q->whereIn('condition', (array) $request->phone_condition);
                }
                if ($request->filled('device_type')) {
                    $q->whereIn('device', (array) $request->device_type);
                }
                foreach (['ram' => 'phone_ram', 'storage' => 'phone_storage'] as $spec => $param) {
                    if ($request->filled($param)) {
                        $q->whereIn($spec, (array) $request->input($param));
                    }
                }
            });
        }

        // Laptop/computer spec filters — only run when at least one spec param is present
        if ($hasComputerFilters) {
            $query->whereHas('computer', function ($q) use ($request) {
                foreach (['processor', 'ram', 'storage'] as $spec) {
                    if ($request->filled($spec)) {
                        $q->whereIn($spec, (array) $request->input($spec));
                    }
                }
            });
        }

        $adverts = $query->orderWithFeatured()
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $adverts->items(),
            'pagination' => [
                'current_page' => $adverts->currentPage(),
                'last_page'    => $adverts->lastPage(),
                'per_page'     => $adverts->perPage(),
                'total'        => $adverts->total(),
                'has_more'     => $adverts->hasMorePages(),
            ],
            'filters_applied' => array_filter([
                'keyword'        => $keyword,
                'category'       => $request->input('category'),
                'sub_category'   => $request->input('sub_category'),
                'brand'          => $request->input('brand'),
                'model'          => $request->input('model'),
                'location'       => $request->input('location'),
                'min'            => $request->input('min'),
                'max'            => $request->input('max'),
                'range'          => $request->input('range'),
                'buydirect'      => $request->input('buydirect'),
                'seller'         => $request->input('seller'),
                'item_condition' => $request->input('item_condition'),
                'car_condition'  => $request->input('car_condition'),
                'fuel_type'      => $request->input('fuel_type'),
                'transmission'   => $request->input('transmission'),
                'registration'   => $request->input('registration'),
                'phone_condition'=> $request->input('phone_condition'),
                'device_type'    => $request->input('device_type'),
                'phone_ram'      => $request->input('phone_ram'),
                'phone_storage'  => $request->input('phone_storage'),
                'processor'      => $request->input('processor'),
                'ram'            => $request->input('ram'),
                'storage'        => $request->input('storage'),
            ], fn($v) => $v !== null && $v !== ''),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/search/filter-by-seller",
     *     summary="Filter by verified/unverified sellers",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="sellers", type="string", enum={"all", "yes", "no"}, description="Seller verification status")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Filtered results")
     * )
     */
    public function filterBySeller(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        $sellers = $request->input('sellers', 'all');
        if ($sellers !== 'all') {
            $query->whereHas('owner', function ($q) use ($sellers) {
                $q->where('verified', $sellers);
            });
        }

        // Context filters
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

        $adverts = $query->orderWithFeatured()
                        ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'pagination' => [
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
                'has_more' => $adverts->hasMorePages()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/search/filter-by-buydirect",
     *     summary="Filter by buy direct availability",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="buydirect", type="string", description="Buy direct filter value")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Filtered results")
     * )
     */
    public function filterByBuydirect(Request $request)
    {
        $query = Advert::with('firstImage')
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        // Context filters
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

        $adverts = $query->orderWithFeatured()
                        ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'pagination' => [
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
                'has_more' => $adverts->hasMorePages()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/location/{location}/{slug}",
     *     summary="Search by location and category/subcategory/brand",
     *     tags={"Search"},
     *     @OA\Parameter(name="location", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Location-based search results")
     * )
     */
    public function locationSearch($location, $slug)
    {
        // Determine type based on slug existence
        if (Category::where('category_slug', $slug)->exists()) {
            return $this->locationCategory($location, $slug);
        } elseif (SubCategory::where('sub_cat_slug', $slug)->exists()) {
            return $this->locationSubcat($location, $slug);
        } elseif (Brands::where('brand_slug', $slug)->exists()) {
            return $this->locationBrand($location, $slug);
        } elseif (VehicleModel::where('model_slug', $slug)->exists()) {
            return $this->locationModel($location, $slug);
        }

        return response()->json([
            'success' => false,
            'message' => 'Resource not found'
        ], 404);
    }

    /**
     * Location + Category search
     */
    private function locationCategory($location, $slug)
    {
        $cat = Category::where('category_slug', $slug)->first();

        if (!$cat) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('state', $location)
                    ->where('category', $cat->id)
                    ->orderWithFeatured()
                    ->paginate(request()->input('per_page', 10));

        $count_cat = Advert::activeNotRecentlySold()
                          ->where('category', $cat->id)
                          ->count();

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'category' => $cat,
            'total_count' => $count_cat,
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ]
        ]);
    }

    /**
     * Location + Subcategory search
     */
    private function locationSubcat($location, $slug)
    {
        $subcat = SubCategory::where('sub_cat_slug', $slug)->first();

        if (!$subcat) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('sold', 'No')
                    ->where('state', $location)
                    ->where('sub_category', $subcat->id)
                    ->orderWithFeatured()
                    ->paginate(request()->input('per_page', 10));

        $count_subcat = Advert::activeNotRecentlySold()
                            ->where('sub_category', $subcat->id)
                            ->count();

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'subcategory' => $subcat,
            'total_count' => $count_subcat,
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ]
        ]);
    }

    /**
     * Location + Brand search
     */
    private function locationBrand($location, $slug)
    {
        $brand = Brands::where('brand_slug', $slug)->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('state', $location)
                    ->where('brand', $brand->id)
                    ->orderWithFeatured()
                    ->paginate(request()->input('per_page', 10));

        $count_brand = Advert::activeNotRecentlySold()
                        ->where('brand', $brand->id)
                        ->count();

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'brand' => $brand,
            'total_count' => $count_brand,
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/load-more",
     *     summary="Load more results for infinite scroll",
     *     tags={"Search"},
     *     @OA\Parameter(name="product", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sub_category", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="brand", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="location", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="buydirect", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="More results")
     * )
     */
    private function locationModel($location, $slug)
    {
        $model = VehicleModel::with('brand')->where('model_slug', $slug)->first();

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('state', $location)
                    ->ofModel($model->id)
                    ->orderWithFeatured()
                    ->paginate(request()->input('per_page', 10));

        $count_model = Advert::activeNotRecentlySold()
                        ->ofModel($model->id)
                        ->count();

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'model' => $model->only(['id', 'model', 'model_slug', 'brand_id']),
            'brand' => $model->brand,
            'total_count' => $count_model,
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
                'has_more' => $ads->hasMorePages()
            ]
        ]);
    }

    public function loadMore(Request $request)
    {
        $filterService = new FilterService();
        $query = Advert::with('firstImage')->activeNotRecentlySold();

        if ($request->filled('product')) {
            $query->where(function ($q) use ($request) {
                $q->where('ad_title', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('ad_id', 'LIKE', '%' . $request->product . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('model')) {
            $query->ofModel($request->model);
        }

        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        $filterService->applyConditionFilter($query, $request);

        $ads = $query->orderWithFeatured()
                    ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $ads->items(),
            'pagination' => [
                'current_page' => $ads->currentPage(),
                'last_page'    => $ads->lastPage(),
                'per_page'     => $ads->perPage(),
                'total'        => $ads->total(),
                'has_more'     => $ads->hasMorePages(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/filters",
     *     summary="Get available filter options",
     *     description="Includes phone_specs (ram, storage → send as phone_ram / phone_storage to POST /api/search/filter) and computer_specs (processor, ram, storage, storage_type → send as processor / ram / storage). ?brand={id} adds that brand's models.",
     *     tags={"Search"},
     *     @OA\Response(response=200, description="Filter options")
     * )
     */
    public function getFilters(Request $request)
    {
        // ?brand={id}: that brand's models with active listing counts (e.g. Apple → iPhones)
        $brand = $request->filled('brand') ? Brands::with('subCategory')->find($request->brand) : null;
        $models = $brand && $brand->subCategory
            ? (new AdvertQueryService())->getModelsForBrand($brand->id, $brand->subCategory->cat_id)
            : [];

        return response()->json([
            'success' => true,
            'data' => [
                'models' => $models,
                'categories' => Category::with('subCategories')->get(),
                'states' => State::all(),
                'price_ranges' => [
                    ['value' => 'under_20k', 'label' => 'Under Br 2,000'],
                    ['value' => '20k_120k', 'label' => 'Br 2,000 - Br 10,000'],
                    ['value' => '120k_1m', 'label' => 'Br 10,000 - Br 100,000'],
                    ['value' => '1m_10m', 'label' => 'Br 100,000 - Br 1,000,000'],
                    ['value' => 'above_10m', 'label' => 'Above Br 1,000,000'],
                ],
                'seller_types' => [
                    ['value' => 'all', 'label' => 'All Sellers'],
                    ['value' => 'yes', 'label' => 'Verified Sellers'],
                    ['value' => 'no', 'label' => 'Unverified Sellers'],
                ],
                'conditions' => [
                    'general' => ['New', 'Foreign Used', 'Locally Used'],
                    'vehicles' => ['Local used', 'Foreign used'],
                    'phones'   => [
                        'New - Unboxed',
                        'New - No Packaging',
                        'Used - Very Good',
                        'Used - Good',
                        'Used - Defect',
                        'Foreign Used - No Packaging',
                    ],
                ],
                // Mobile Phones RAM / storage — filter with phone_ram / phone_storage
                'phone_specs' => PhoneSpecs::allOptions(),
                // Laptops & Computers specs — filter with processor / ram / storage
                'computer_specs' => ComputerSpecs::allOptions(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search/suggestions",
     *     summary="Get search suggestions",
     *     tags={"Search"},
     *     @OA\Parameter(name="q", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Search suggestions")
     * )
     */
    public function getSuggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = $request->input('q');

        $suggestions = Advert::where('ad_title', 'LIKE', '%' . $query . '%')
                            ->where('ad_status', 'active')
                            ->where('sold', 'No')
                            ->select('ad_title')
                            ->distinct()
                            ->limit(10)
                            ->pluck('ad_title');

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Filter adverts by car details
     *
     * @OA\Post(
     *     path="/api/search/filter-by-car",
     *     summary="Filter adverts by car-specific details",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="condition", type="string", description="Car condition", example="Locally Used"),
     *             @OA\Property(property="fuel_type", type="string", description="Fuel type", example="Petrol"),
     *             @OA\Property(property="transmission", type="string", description="Transmission type", example="Automatic"),
     *             @OA\Property(property="registration", type="string", description="Registration status", example="Registered"),
     *             @OA\Property(property="category", type="integer", description="Category ID"),
     *             @OA\Property(property="sub_category", type="integer", description="Sub-category ID"),
     *             @OA\Property(property="brand", type="integer", description="Brand ID"),
     *             @OA\Property(property="location", type="string", description="State/Location"),
     *             @OA\Property(property="min", type="integer", description="Minimum price"),
     *             @OA\Property(property="max", type="integer", description="Maximum price"),
     *             @OA\Property(property="per_page", type="integer", description="Items per page", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered car adverts",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function filterByCarDetails(Request $request)
    {
        $filterService = new FilterService();

        $query = Advert::with(['firstImage', 'user', 'car'])
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        // Apply standard filters (category, location, price)
        $filterService->applyContextFilters($query, $request);
        $filterService->applyPriceFilters($query, $request);

        // Apply car-specific filters
        if ($request->hasAny(['condition', 'fuel_type', 'transmission', 'registration'])) {
            $filterService->applyCarFilters($query, $request);
        }

        $perPage = $request->input('per_page', 20);
        $adverts = $query->orderWithFeatured()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'pagination' => [
                'total' => $adverts->total(),
                'per_page' => $adverts->perPage(),
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'from' => $adverts->firstItem(),
                'to' => $adverts->lastItem(),
                'has_more' => $adverts->hasMorePages()
            ],
            'filters_applied' => [
                'condition' => $request->input('condition'),
                'fuel_type' => $request->input('fuel_type'),
                'transmission' => $request->input('transmission'),
                'registration' => $request->input('registration'),
                'category' => $request->input('category'),
                'location' => $request->input('location'),
                'price_range' => [
                    'min' => $request->input('min'),
                    'max' => $request->input('max')
                ]
            ]
        ]);
    }

    /**
     * Filter adverts by phone details
     *
     * @OA\Post(
     *     path="/api/search/filter-by-phone",
     *     summary="Filter adverts by phone-specific details",
     *     tags={"Search"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="condition", type="string", description="Phone condition", example="Brand New"),
     *             @OA\Property(property="device_type", type="string", description="Device type", example="Smartphone"),
     *             @OA\Property(property="category", type="integer", description="Category ID"),
     *             @OA\Property(property="sub_category", type="integer", description="Sub-category ID"),
     *             @OA\Property(property="brand", type="integer", description="Brand ID"),
     *             @OA\Property(property="location", type="string", description="State/Location"),
     *             @OA\Property(property="min", type="integer", description="Minimum price"),
     *             @OA\Property(property="max", type="integer", description="Maximum price"),
     *             @OA\Property(property="per_page", type="integer", description="Items per page", example=20)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered phone adverts",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function filterByPhoneDetails(Request $request)
    {
        $filterService = new FilterService();

        $query = Advert::with(['firstImage', 'user', 'phone'])
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        // Apply standard filters (category, location, price)
        $filterService->applyContextFilters($query, $request);
        $filterService->applyPriceFilters($query, $request);

        // Apply phone-specific filters
        if ($request->hasAny(['condition', 'device_type'])) {
            $filterService->applyPhoneFilters($query, $request);
        }

        $perPage = $request->input('per_page', 20);
        $adverts = $query->orderWithFeatured()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'pagination' => [
                'total' => $adverts->total(),
                'per_page' => $adverts->perPage(),
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'from' => $adverts->firstItem(),
                'to' => $adverts->lastItem(),
                'has_more' => $adverts->hasMorePages()
            ],
            'filters_applied' => [
                'condition' => $request->input('condition'),
                'device_type' => $request->input('device_type'),
                'category' => $request->input('category'),
                'location' => $request->input('location'),
                'price_range' => [
                    'min' => $request->input('min'),
                    'max' => $request->input('max')
                ]
            ]
        ]);
    }
}
