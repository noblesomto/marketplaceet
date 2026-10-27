<?php

namespace App\Http\Controllers\Shop;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Advert;
use App\Models\AdvertImage;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Brands;
use App\Models\User;
use App\Models\State;
use App\Models\VehicleModel;
use Jenssegers\Agent\Agent;
use App\Traits\HasUserSession;
use App\Traits\GeneratesSeoMeta;
use App\Services\FilterService;
use App\Services\AdvertQueryService;
use App\Services\SlugRedirectResolver;

class SearchFilter extends Controller
{
    use HasUserSession, GeneratesSeoMeta;

    public function __construct(
        private FilterService $filterService,
        private AdvertQueryService $advertQueryService,
        private SlugRedirectResolver $slugRedirects,
    ) {}

    public function search(Request $request)
    {
        $title = config('global.site_name').' | '.config('global.site_title');

        $request->validate([
            'product' => 'nullable|string|min:3',
            'location' => 'nullable',
            'category' => 'nullable',
        ]);

        // Start with active ads
        $query = Advert::with(['firstImage', 'owner'])
                    ->activeNotRecentlySold();

        // Add conditions only if the parameter is provided
        if ($request->filled('product')) {
            $query->where(function ($q) use ($request) {
                $q->where('ad_title', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('title_slug', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('ad_id', $request->product);
            });
        }

        $this->filterService->applyContextFilters($query, $request);

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        // Order and paginate results
        $ads = $query->orderWithFeatured()
             ->paginate(20)
             ->appends($request->except('page'));

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        $hasMore = $ads->hasMorePages();

        $searchParams = [
            'product' => $request->input('product'),
            'location' => $request->input('location'),
            'category' => $request->input('category'),
            'sub_category' => $request->input('sub_category'),
            'brand' => $request->input('brand'),
            'buydirect' => $request->input('buydirect'),
        ];
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = 'noindex, follow';
        return view('public.adverts', compact('title', 'ads', 'user', 'categories', 'hasMore', 'searchParams','isMobile', 'metaRobots'));
    }

    /**
     * Match adverts against a location URL segment. adverts.state_slug is
     * LGA-granular (e.g. "ikeja"), while adverts.state holds the full state
     * name (e.g. "Lagos", "Akwa Ibom"). A state-level URL segment is always a
     * hyphenated slug ("akwa-ibom"), which can never equal a multi-word state
     * name via plain string comparison — so it must be resolved through the
     * states table first.
     */
    private function applyLocationFilter($q, string $location): void
    {
        $q->where('state', $location)->orWhere('state_slug', $location);

        if ($stateName = State::nameForSlug($location)) {
            $q->orWhere('state', $stateName);
        }
    }

    public function location_router($location, $slug)
    {
        if (Category::where('category_slug', $slug)->exists()) {
            return $this->location_category(request(), $location, $slug);
        } elseif (SubCategory::where('sub_cat_slug', $slug)->exists()) {
            return $this->location_subcat(request(), $location, $slug);
        } elseif (Brands::where('brand_slug', $slug)->exists()) {
            return $this->location_brand(request(), $location, $slug);
        }

        if (($newSlug = $this->slugRedirects->resolve('subcategory', $slug)) && SubCategory::where('sub_cat_slug', $newSlug)->exists()) {
            return redirect("/{$location}/{$newSlug}", 301);
        }

        if (($newSlug = $this->slugRedirects->resolve('brand', $slug)) && Brands::where('brand_slug', $newSlug)->exists()) {
            return redirect("/{$location}/{$newSlug}", 301);
        }

        abort(404);
    }


    public function location_category(Request $request, $location, $slug)
    {
        $cat = Category::where('category_slug', $slug)->firstOrFail();

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where(function ($q) use ($location) {
                        $this->applyLocationFilter($q, $location);
                    })
                    ->where('category', $cat->id)
                    ->orderWithFeatured()
                    ->paginate(20);

        $locationCount = $ads->total();
        $seo   = $this->buildSeoMeta($cat->category, $cat->seo_group ?? 'product', url("/{$location}/{$slug}"), $this->resolveLocationDisplayName($location), $locationCount);
        $title = $seo['seoTitle'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_cat = Advert::activeNotRecentlySold()
                          ->where('category', $cat->id)
                          ->count();

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($locationCount);
        $brandsForCategory = $this->advertQueryService->getBrandsForCategory($cat->id, $location);
        $qualifyingPriceRanges = $this->getQualifyingPriceRanges($cat, $location);
        $qualifyingConditions = $this->getQualifyingConditions($cat, $location);

        return view('public.category', array_merge(compact('title', 'ads', 'user', 'cat', 'categories', 'count_cat', 'hasMore', 'isMobile', 'location', 'metaRobots', 'brandsForCategory', 'qualifyingPriceRanges', 'qualifyingConditions'), $seo));
    }

    /**
     * Which of the 5 fixed price-range slugs have enough inventory (same
     * threshold as seoRobotsForCount) to be worth linking to from the
     * category page's "Shop by Price" sidebar. Bounded to 5 checks — no
     * combinatorial blow-up since the bucket count is fixed.
     */
    private function getQualifyingPriceRanges(Category $cat, string $location): array
    {
        // One query with a conditional SUM per bucket instead of 5 separate
        // COUNT queries — bucket boundaries mirror FilterService::applyPriceRange().
        $counts = Advert::activeNotRecentlySold()
            ->where(function ($q) use ($location) {
                $this->applyLocationFilter($q, $location);
            })
            ->where('category', $cat->id)
            ->selectRaw("
                SUM(CASE WHEN price < 2000 THEN 1 ELSE 0 END) as under_20k,
                SUM(CASE WHEN price BETWEEN 2000 AND 10000 THEN 1 ELSE 0 END) as `20k_120k`,
                SUM(CASE WHEN price BETWEEN 10000 AND 100000 THEN 1 ELSE 0 END) as `120k_1m`,
                SUM(CASE WHEN price BETWEEN 100000 AND 1000000 THEN 1 ELSE 0 END) as `1m_10m`,
                SUM(CASE WHEN price > 1000000 THEN 1 ELSE 0 END) as above_10m
            ")
            ->first();

        $qualifying = [];

        foreach (self::PRICE_RANGE_SLUGS as $slug => $range) {
            if (($counts->{$range['key']} ?? 0) >= 5) {
                $qualifying[$slug] = $range['label'];
            }
        }

        return $qualifying;
    }

    /**
     * Which condition slugs (if any apply to this category) have enough
     * inventory to be worth linking to from the "Shop by Condition" sidebar.
     */
    private function getQualifyingConditions(Category $cat, string $location): array
    {
        $conditionMap = $this->conditionSlugsForCategory($cat->id);

        if (!$conditionMap) {
            return [];
        }

        // One grouped query instead of up to 3 separate COUNT queries.
        $baseQuery = Advert::activeNotRecentlySold()
            ->where(function ($q) use ($location) {
                $this->applyLocationFilter($q, $location);
            })
            ->where('category', $cat->id);

        if ($cat->id === 1) {
            $counts = (clone $baseQuery)
                ->join('car_details', 'car_details.advert_id', '=', 'adverts.id')
                ->selectRaw('car_details.condition as value, COUNT(*) as total')
                ->groupBy('car_details.condition')
                ->pluck('total', 'value');
        } else {
            $counts = (clone $baseQuery)
                ->selectRaw('item_condition as value, COUNT(*) as total')
                ->groupBy('item_condition')
                ->pluck('total', 'value');
        }

        $qualifying = [];

        foreach ($conditionMap as $slug => $value) {
            if (($counts[$value] ?? 0) >= 5) {
                $qualifying[$slug] = $value;
            }
        }

        return $qualifying;
    }

    public function location_subcat(Request $request, $location, $slug)
    {
        $subcat = SubCategory::where('sub_cat_slug', $slug)->firstOrFail();

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where('sold', 'No')
                    ->where(function ($q) use ($location) {
                        $this->applyLocationFilter($q, $location);
                    })
                    ->where('sub_category', $subcat->id)
                    ->orderWithFeatured()
                    ->paginate(20);

        $locationCount = $ads->total();

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_subcat = Advert::activeNotRecentlySold()
                            ->where('sub_category', $subcat->id)
                            ->count();

        $brands = $this->advertQueryService->getBrandsForSubcat($subcat->id);

        $cat   = $subcat->category;
        $seo   = $this->buildSeoMeta($subcat->sub_category, $cat->seo_group ?? 'product', url("/{$location}/{$slug}"), $this->resolveLocationDisplayName($location), $locationCount);
        $title = $seo['seoTitle'];

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($locationCount);

        return view('public.sub-category', array_merge(compact('title', 'ads', 'user', 'categories', 'subcat', 'count_subcat', 'hasMore', 'isMobile', 'location', 'brands', 'cat', 'metaRobots'), $seo));
    }

    public function location_brand(Request $request, $location, $slug)
    {
        $brand = Brands::where('brand_slug', $slug)->firstOrFail();

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where(function ($q) use ($location) {
                        $this->applyLocationFilter($q, $location);
                    })
                    ->where('brand', $brand->id)
                    ->orderWithFeatured()
                    ->paginate(20);

        $locationCount = $ads->total();

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_brand = Advert::activeNotRecentlySold()
                        ->where('brand', $brand->id)
                        ->count();

        $subcat = $brand->subcategory;
        $cat = $subcat ? $subcat->category : null;

        $brands = $this->advertQueryService->getBrandsForSubcat($brand->subcat_id);

        $count_subcat = Advert::activeNotRecentlySold()
                        ->where('sub_category', $brand->subcat_id)
                        ->count();

        $seoName = $brand->brand . ($subcat ? ' ' . $subcat->sub_category : '');
        $seoGrp  = $cat->seo_group ?? 'product';
        $seo     = $this->buildSeoMeta($seoName, $seoGrp, url("/{$location}/{$slug}"), $this->resolveLocationDisplayName($location), $locationCount);
        $title   = $seo['seoTitle'];

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($locationCount);

        return view('public.brand', array_merge(compact('title', 'ads', 'user', 'categories', 'brand', 'count_brand', 'hasMore', 'isMobile', 'location', 'brands', 'cat', 'subcat', 'count_subcat', 'metaRobots'), $seo));
    }

    /**
     * Only these categories have a model-bearing detail table — model landing
     * pages only exist for them.
     */
    private const MODEL_CATEGORY_DETAIL_RELATIONS = Advert::MODEL_DETAIL_RELATIONS;

    /**
     * Fixed set of price-range slugs, reusing FilterService's existing
     * buckets — see FilterService::applyPriceRange().
     */
    private const PRICE_RANGE_SLUGS = [
        'under-20k' => ['key' => 'under_20k', 'label' => 'Under Br 2,000'],
        '20k-120k'  => ['key' => '20k_120k',  'label' => 'Br 2,000 - Br 10,000'],
        '120k-1m'   => ['key' => '120k_1m',   'label' => 'Br 10,000 - Br 100,000'],
        '1m-10m'    => ['key' => '1m_10m',    'label' => 'Br 100,000 - Br 1 Million'],
        'above-10m' => ['key' => 'above_10m', 'label' => 'Above Br 1 Million'],
    ];

    /**
     * Condition slugs for Vehicles (category 1) — car_details.condition has
     * clean, reliable values.
     */
    private const VEHICLE_CONDITION_SLUGS = [
        'brand-new'    => 'Brand new',
        'foreign-used' => 'Foreign used',
        'locally-used' => 'Local used',
    ];

    /**
     * Condition slugs for every category except Vehicles (1) and Mobile
     * Phones & Tablets (4) — adverts.item_condition has clean, reliable
     * values. Phones are deliberately excluded: phone_details.condition is
     * a messy free-text field ("Used - Good", "Used - Defect", etc.) that
     * doesn't reliably indicate foreign vs. locally-used, so condition
     * pages for phones need a data cleanup decision before they can exist.
     */
    private const GENERAL_CONDITION_SLUGS = [
        'new'          => 'New',
        'foreign-used' => 'Foreign Used',
        'locally-used' => 'Locally Used',
    ];

    /**
     * Dispatcher for /{location}/{category_slug}/{slug} — the 3rd segment
     * can be a brand, a model (vehicles/mobile-phones only), a fixed
     * price-range slug, or a fixed condition slug (all categories except
     * mobile phones). All share the same URL shape, so they're tried in
     * order rather than routed separately.
     */
    public function location_category_brand(Request $request, $location, $category_slug, $slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();

        $brand = Brands::where('brand_slug', $slug)
            ->whereHas('subCategory', fn ($q) => $q->where('cat_id', $cat->id))
            ->first();

        if ($brand) {
            return $this->renderLocationCategoryBrand($request, $location, $category_slug, $cat, $slug, $brand);
        }

        if (isset(self::MODEL_CATEGORY_DETAIL_RELATIONS[$cat->id])) {
            $model = VehicleModel::where('model_slug', $slug)
                ->whereHas('brand.subCategory', fn ($q) => $q->where('cat_id', $cat->id))
                ->first();

            if ($model) {
                return $this->renderLocationCategoryModel($request, $location, $category_slug, $cat, $slug, $model);
            }
        }

        if (isset(self::PRICE_RANGE_SLUGS[$slug])) {
            return $this->renderLocationCategoryPriceRange($request, $location, $category_slug, $cat, $slug);
        }

        $conditionMap = $this->conditionSlugsForCategory($cat->id);
        if ($conditionMap && isset($conditionMap[$slug])) {
            return $this->renderLocationCategoryCondition($request, $location, $category_slug, $cat, $slug, $conditionMap[$slug]);
        }

        if ($newSlug = $this->slugRedirects->resolve('brand', $slug)) {
            $redirectBrand = Brands::where('brand_slug', $newSlug)
                ->whereHas('subCategory', fn ($q) => $q->where('cat_id', $cat->id))
                ->first();

            if ($redirectBrand) {
                return redirect("/{$location}/{$category_slug}/{$newSlug}", 301);
            }

            // A retired brand that stood for a whole subcategory (Laptops & Computers → "Laptop")
            if (SubCategory::where('sub_cat_slug', $newSlug)->where('cat_id', $cat->id)->exists()) {
                return redirect("/{$location}/{$category_slug}", 301);
            }
        }

        if (isset(self::MODEL_CATEGORY_DETAIL_RELATIONS[$cat->id])) {
            if ($newSlug = $this->slugRedirects->resolve('model', $slug)) {
                $redirectModel = VehicleModel::where('model_slug', $newSlug)
                    ->whereHas('brand.subCategory', fn ($q) => $q->where('cat_id', $cat->id))
                    ->first();

                if ($redirectModel) {
                    return redirect("/{$location}/{$category_slug}/{$newSlug}", 301);
                }
            }
        }

        abort(404);
    }

    /**
     * Which condition slug map (if any) applies to a category. Returns null
     * for Mobile Phones & Tablets (4) — see GENERAL_CONDITION_SLUGS docblock.
     */
    private function conditionSlugsForCategory(int $categoryId): ?array
    {
        if ($categoryId === 4) {
            return null;
        }

        return $categoryId === 1 ? self::VEHICLE_CONDITION_SLUGS : self::GENERAL_CONDITION_SLUGS;
    }

    private function renderLocationCategoryBrand(Request $request, $location, $category_slug, Category $cat, string $brand_slug, Brands $brand)
    {
        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where(function ($q) use ($location) {
                        $this->applyLocationFilter($q, $location);
                    })
                    ->where('category', $cat->id)
                    ->where('brand', $brand->id)
                    ->orderWithFeatured()
                    ->paginate(20);

        $locationCount = $ads->total();

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_brand = Advert::activeNotRecentlySold()
                        ->where('brand', $brand->id)
                        ->count();

        $subcat = $brand->subcategory;

        $brands = $this->advertQueryService->getBrandsForSubcat($brand->subcat_id);

        $count_subcat = Advert::activeNotRecentlySold()
                        ->where('sub_category', $brand->subcat_id)
                        ->count();

        $models = isset(self::MODEL_CATEGORY_DETAIL_RELATIONS[$cat->id])
            ? $this->advertQueryService->getModelsForBrand($brand->id, $cat->id, $location)
            : collect();

        $seoName = $brand->brand . ' ' . $cat->category;
        $seo     = $this->buildSeoMeta($seoName, $cat->seo_group ?? 'product', url("/{$location}/{$category_slug}/{$brand_slug}"), $this->resolveLocationDisplayName($location), $locationCount);
        $title   = $seo['seoTitle'];

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($locationCount);

        return view('public.brand', array_merge(compact('title', 'ads', 'user', 'categories', 'brand', 'count_brand', 'hasMore', 'isMobile', 'location', 'brands', 'cat', 'subcat', 'count_subcat', 'metaRobots', 'models'), $seo));
    }

    private function renderLocationCategoryModel(Request $request, $location, $category_slug, Category $cat, string $model_slug, VehicleModel $model)
    {
        $detailRelation = self::MODEL_CATEGORY_DETAIL_RELATIONS[$cat->id];

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where(function ($q) use ($location) {
                        $this->applyLocationFilter($q, $location);
                    })
                    ->where('category', $cat->id)
                    ->whereHas($detailRelation, fn ($q) => $q->where('model', $model->id))
                    ->orderWithFeatured()
                    ->paginate(20);

        $locationCount = $ads->total();

        $brand = $model->brand;

        $seoName = $brand->brand . ' ' . $model->model;
        $seo     = $this->buildSeoMeta($seoName, $cat->seo_group ?? 'product', url("/{$location}/{$category_slug}/{$model_slug}"), $this->resolveLocationDisplayName($location), $locationCount);
        $title   = $seo['seoTitle'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($locationCount);

        // Deeper browsing beyond page 1 goes to the parent brand page, which
        // already has full "load more" pagination wired up.
        $moreUrl = url("/{$location}/{$category_slug}/{$brand->brand_slug}");

        return view('public.location-attribute', array_merge(compact('title', 'ads', 'user', 'categories', 'location', 'metaRobots', 'hasMore', 'isMobile', 'moreUrl'), $seo));
    }

    private function renderLocationCategoryPriceRange(Request $request, $location, $category_slug, Category $cat, string $rangeSlug)
    {
        $range = self::PRICE_RANGE_SLUGS[$rangeSlug];

        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where(function ($q) use ($location) {
                        $this->applyLocationFilter($q, $location);
                    })
                    ->where('category', $cat->id)
                    ->tap(fn ($q) => $this->filterService->applyPriceRange($q, $range['key']))
                    ->orderWithFeatured()
                    ->paginate(20);

        $locationCount = $ads->total();

        $seoName = $cat->category . ' ' . $range['label'];
        $seo     = $this->buildSeoMeta($seoName, $cat->seo_group ?? 'product', url("/{$location}/{$category_slug}/{$rangeSlug}"), $this->resolveLocationDisplayName($location), $locationCount);
        $title   = $seo['seoTitle'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($locationCount);

        $moreUrl = url("/{$location}/{$category_slug}");

        return view('public.location-attribute', array_merge(compact('title', 'ads', 'user', 'categories', 'location', 'metaRobots', 'hasMore', 'isMobile', 'moreUrl'), $seo));
    }

    private function renderLocationCategoryCondition(Request $request, $location, $category_slug, Category $cat, string $conditionSlug, string $conditionValue)
    {
        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->where(function ($q) use ($location) {
                        $this->applyLocationFilter($q, $location);
                    })
                    ->where('category', $cat->id)
                    ->when(
                        $cat->id === 1,
                        fn ($q) => $q->whereHas('car', fn ($cq) => $cq->where('condition', $conditionValue)),
                        fn ($q) => $q->where('item_condition', $conditionValue)
                    )
                    ->orderWithFeatured()
                    ->paginate(20);

        $locationCount = $ads->total();

        $seoName = ucwords($conditionValue) . ' ' . $cat->category;
        $seo     = $this->buildSeoMeta($seoName, $cat->seo_group ?? 'product', url("/{$location}/{$category_slug}/{$conditionSlug}"), $this->resolveLocationDisplayName($location), $locationCount);
        $title   = $seo['seoTitle'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        $hasMore = $ads->hasMorePages();
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($locationCount);

        $moreUrl = url("/{$location}/{$category_slug}");

        return view('public.location-attribute', array_merge(compact('title', 'ads', 'user', 'categories', 'location', 'metaRobots', 'hasMore', 'isMobile', 'moreUrl'), $seo));
    }

    public function filter(Request $request)
    {
        $query = Advert::with(['firstImage', 'owner'])
                    ->where('ad_status', 'active')
                    ->where('sold', 'No');

        $this->filterService->applyContextFilters($query, $request);
        $this->filterService->applyPriceFilters($query, $request);

        $adverts = $query->orderWithFeatured()
                 ->paginate(20)
                 ->appends($request->except('page'));

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }

    public function filterBySeller(Request $request)
    {
        $query = Advert::with(['firstImage', 'owner'])
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        // Seller filter
        $sellers = $request->input('sellers', 'all');
        if ($sellers !== 'all') {
            $query->whereHas('owner', function ($q) use ($sellers) {
                $q->where('verified', $sellers);
            });
        }

        $this->filterService->applyContextFilters($query, $request);

        $adverts = $query->orderWithFeatured()
                 ->paginate(20)
                 ->appends(['sellers' => $sellers]);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }

    public function filterByBuydirect(Request $request)
    {
        $query = Advert::with(['firstImage', 'owner'])
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        $this->filterService->applyContextFilters($query, $request);

        $adverts = $query->orderWithFeatured()
                 ->paginate(20)
                 ->appends(['buydirect' => $request->input('buydirect')]);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }

    public function loadMore(Request $request)
    {
        $query = Advert::with(['firstImage', 'owner'])
                    ->activeNotRecentlySold();

        // Product search
        if ($request->filled('product')) {
            $query->where(function ($q) use ($request) {
                $q->where('ad_title', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('title_slug', 'LIKE', '%' . $request->product . '%')
                  ->orWhere('ad_id', $request->product);
            });
        }

        $this->filterService->applyContextFilters($query, $request);

        if ($request->filled('buydirect')) {
            $query->where('buy_direct', $request->buydirect);
        }

        $ads = $query->orderWithFeatured()->paginate(20);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($ads),
            'hasMore' => $ads->hasMorePages(),
        ]);
    }

    public function filterByCarDetails(Request $request)
    {
        $adverts = $this->filterService->buildCarDetailQuery($request)->paginate(20);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }

    public function filterByPhoneDetails(Request $request)
    {
        $adverts = $this->filterService->buildPhoneDetailQuery($request)->paginate(20);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }

    public function filterByComputerDetails(Request $request)
    {
        $adverts = $this->filterService->buildComputerDetailQuery($request)->paginate(20);

        return response()->json([
            'html'    => $this->filterService->renderAdvertCards($adverts),
            'hasMore' => $adverts->hasMorePages(),
        ]);
    }
}
