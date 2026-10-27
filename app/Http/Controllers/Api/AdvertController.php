<?php

namespace App\Http\Controllers\Api;

use App\Services\BuyDirectCheckout;
use App\Services\BuyDirectSettings;
use App\Http\Controllers\Controller;
use App\Models\Advert;
use App\Models\AdvertImage;
use App\Models\Brands;
use App\Models\CarDetail;
use App\Models\Category;
use App\Models\VehicleModel;
use App\Models\PhoneDetail;
use App\Models\ComputerDetail;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Reports;
use App\Models\State;
use App\Models\Message;
use App\Models\GigLogistic;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportMail;
use App\Services\AdvertQueryService;
use App\Services\FeaturedAdPaginator;
use App\Support\AdvertVisibility;


/**
 * @group Adverts
 *
 * Public APIs for viewing and browsing adverts
 */
class AdvertController extends Controller
{
    /**
     * Get all adverts with pagination and categorized sections
     * GET /api/adverts
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 30);
        $currentPage = $request->get('page', 1);
        $section = $request->get('section', 'all');

        $response = ['success' => true, 'data' => []];

        // Gallery - Random active ads
        if ($section === 'all' || $section === 'gallery') {
            $response['data']['gallery'] = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->limit(10)
                ->get();
        }

        // Featured ads
        if ($section === 'all' || $section === 'featured') {
            $featured = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where('featured', 'yes')
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->limit(10)
                ->get();

            $response['data']['featured'] = $featured;
        } else {
            $featured = collect();
        }

        // Main listings
        if ($section === 'all' || !$section) {
            $excludeIds = $featured->pluck('id')->toArray();
            $recentCount = (int) ($perPage * 0.3);
            $randomCount = $perPage - $recentCount;

            $recentListings = Advert::with('firstImage', 'owner')
                ->where('ad_status', 'active')
                ->whereNotIn('id', $excludeIds)
                ->where('featured', '!=', 'yes')
                ->where(function ($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function ($q) {
                              $q->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->orderByDesc('created_at')
                ->skip(($currentPage - 1) * $recentCount)
                ->limit($recentCount)
                ->get();

            $excludeIds = array_merge($excludeIds, $recentListings->pluck('id')->toArray());

            $randomListings = Advert::with('firstImage', 'owner')
                ->where('ad_status', 'active')
                ->whereNotIn('id', $excludeIds)
                ->where('featured', '!=', 'yes')
                ->where(function ($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function ($q) {
                              $q->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->inRandomOrder()
                ->limit($randomCount)
                ->get();

            $mixed = collect();
            $maxCount = max($recentListings->count(), $randomListings->count());

            for ($i = 0; $i < $maxCount; $i++) {
                if ($recentListings->has($i)) $mixed->push($recentListings->get($i));
                if ($randomListings->has($i)) $mixed->push($randomListings->get($i));
            }

            $allListings = $currentPage == 1 ? $featured->concat($mixed) : $mixed;

            $totalCount = Advert::where('ad_status', 'active')
                ->where('featured', '!=', 'yes')
                ->where(function ($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function ($q) {
                              $q->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->count();

            if ($currentPage == 1) $totalCount += $featured->count();

            $response['data']['listings'] = [
                'data' => $allListings,
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $totalCount,
                'last_page' => ceil($totalCount / $perPage),
                'has_more' => ($currentPage * $perPage) < $totalCount
            ];
        }

        // Cars section
        if ($section === 'all' || $section === 'cars') {
            $response['data']['cars'] = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where('sub_category', 2)
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get();
        }

        // Phones section
        if ($section === 'all' || $section === 'phones') {
            $response['data']['phones'] = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where('sub_category', 6)
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get();
        }

        // Fashion section
        if ($section === 'all' || $section === 'fashion') {
            $response['data']['fashion'] = Advert::inRandomOrder()
                ->where('ad_status', 'active')
                ->where('category', 5)
                ->where(function($query) {
                    $query->where('sold', '!=', 'Yes')
                          ->orWhere(function($query) {
                              $query->where('sold', 'Yes')
                                    ->whereNotNull('sold_date')
                                    ->where('sold_date', '>=', now()->subDays(30));
                          });
                })
                ->with('firstImage', 'owner')
                ->orderBy('views', 'desc')
                ->limit(10)
                ->get();
        }

        return response()->json($response);
    }

    /**
     * Get advert details
     * GET /api/adverts/{id}
     */
    public function show($id)
    {
        $ad = Advert::with(['owner'])->find($id);

        $reason = AdvertVisibility::reasonUnavailable($ad);

        if ($reason === 'not_found') {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found',
                'reason'  => $reason,
            ], 404);
        }

        if ($reason) {
            return response()->json([
                'success' => false,
                'message' => 'This advert is no longer available.',
                'reason'  => $reason,
            ], 410);
        }

        // Get images with all required conversions
        $images = $ad->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'original' => $media->getUrl(),
                'large' => $media->getUrl('large'),
                'optimized' => $media->getUrl('optimized'),
                'thumbnail' => $media->getUrl('thumbnail'),
                'thumb_sm' => $media->getUrl('thumb-sm'),  // Mobile optimized
                'thumb_md' => $media->getUrl('thumb-md'),  // Desktop optimized
                'order' => $media->getCustomProperty('position', $media->order_column),
                'name' => $media->name,
                'size' => $media->size,
            ];
        })->sortBy('order')->values();

        $data = [
            'ad' => $ad,
            'images' => $images,
            'ad_owner' => User::where('user_id', $ad->user_id)->first(),
            'cat' => Category::find($ad->category),
            'sub_cat' => SubCategory::find($ad->sub_category),
            'brand' => Brands::find($ad->brand),
            'count_ads' => Advert::where('user_id', $ad->user_id)->count()
        ];

        // Car details
        $data['car'] = CarDetail::where('advert_id', $id)->first();
        if ($data['car']) {
            $data['model'] = VehicleModel::find($data['car']->model);
        }

        // Phone details
        $data['phone'] = PhoneDetail::where('advert_id', $id)->first();
        if ($data['phone']) {
            $data['model'] = VehicleModel::find($data['phone']->model);
        }

        // Laptop/computer details
        $data['computer'] = ComputerDetail::where('advert_id', $id)->first();
        if ($data['computer']) {
            $data['model'] = VehicleModel::find($data['computer']->model);
        }


        // Related adverts with images
        $relatedAdverts = Advert::inRandomOrder()
            ->where('user_id', $ad->user_id)
            ->activeNotRecentlySold()
            ->where('id', '!=', $id)
            ->limit(6)
            ->get();

        $data['adverts'] = $relatedAdverts->map(function ($advert) {
            return [
                'id' => $advert->id,
                'ad_id' => $advert->ad_id,
                'ad_title' => $advert->ad_title,
                'price' => $advert->price,
                'price_type' => $advert->price_type,
                'state' => $advert->state,
                'buy_direct' => $advert->buy_direct,
                'sold' => $advert->sold,
                'sold_date' => $advert->sold_date,
                'featured' => $advert->featured,
                'image_large' => $advert->getFirstImageUrl('large'),
                'image_optimized' => $advert->getFirstImageUrl('optimized'),
                'image_thumb' => $advert->getFirstImageUrl('thumbnail'),
            ];
        });

        // Similar adverts with images
        $similarAds = Advert::inRandomOrder()
            ->where(function($q) use ($ad) {
                $q->where('ad_title', 'LIKE', '%' . $ad->ad_title . '%')
                  ->orWhere('category', $ad->category);
            })
            ->where('id', '!=', $id)
            ->activeNotRecentlySold()
            ->where('user_id', '!=', $ad->user_id)
            ->limit(20)
            ->get();

        $data['similar_ads'] = $similarAds->map(function ($advert) {
            return [
                'id' => $advert->id,
                'ad_id' => $advert->ad_id,
                'ad_title' => $advert->ad_title,
                'price' => $advert->price,
                'price_type' => $advert->price_type,
                'state' => $advert->state,
                'buy_direct' => $advert->buy_direct,
                'sold' => $advert->sold,
                'sold_date' => $advert->sold_date,
                'state' => $advert->state,
                'featured' => $advert->featured,
                'image_large' => $advert->getFirstImageUrl('large'),
                'image_optimized' => $advert->getFirstImageUrl('optimized'),
                'image_thumb' => $advert->getFirstImageUrl('thumbnail'),
            ];
        });

        // Increment views
        // Query builder increment, so a view doesn't bump updated_at (the admin disabled list sorts on it)
        DB::table('adverts')->where('id', $id)->increment('views', 1);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get related adverts for a given ad.
     *
     * Returns ads in the SAME category AND same sub-category.
     * For vehicles (category 1) the same car model is ranked highest.
     * Results ordered by relevance:
     *   Cars  → 1) same model + similar title  2) same model  3) similar title  4) rest
     *   Other → 1) similar title  2) rest
     *
     * @group Adverts
     *
     * @urlParam id integer required The ad_id of the source advert. Example: 12345
     * @queryParam page integer Page number. Default: 1. Example: 1
     * @queryParam per_page integer Items per page (max 40). Default: 20. Example: 20
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 10,
     *       "ad_id": 54321,
     *       "ad_title": "Toyota Camry 2020",
     *       "price": 8500000,
     *       "contact_price": "no",
     *       "price_type": "Fixed",
     *       "state": "Addis Ababa",
     *       "state_slug": "addis-ababa",
     *       "title_slug": "toyota-camry-2020",
     *       "category": 1,
     *       "sub_category": 2,
     *       "car_model_id": "15",
     *       "buy_direct": "No",
     *       "featured": "No",
     *       "sold": "No",
     *       "views": 80,
     *       "image_thumb": "https://marketplace.et/...",
     *       "image_optimized": "https://marketplace.et/..."
     *     }
     *   ],
     *   "meta": {
     *     "total": 30,
     *     "page": 1,
     *     "per_page": 20,
     *     "has_more": true,
     *     "source_ad": {
     *       "ad_id": 12345,
     *       "ad_title": "Toyota Camry 2020",
     *       "category": 1,
     *       "sub_category": 2,
     *       "car_model_id": "15"
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Advert not found"
     * }
     */
    public function related(Request $request, $id)
    {
        $ad = Advert::where('ad_id', $id)->first();

        if (!$ad) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found',
            ], 404);
        }

        $perPage = min((int) $request->get('per_page', 20), 40);
        $page    = max(1, (int) $request->get('page', 1));

        // First 2 words of the title for keyword matching
        $titleWords = array_filter(explode(' ', $ad->ad_title));
        $keyword    = implode(' ', array_slice(array_values($titleWords), 0, 2));

        // Progressive fallback — tries each level until results are found:
        //   1. category + sub_category + brand
        //   2. category + sub_category
        //   3. category only
        //   4. any active ads (last resort)
        $baseQuery = (function () use ($ad, $keyword) {
            $titleOrder = ['CASE WHEN ad_title LIKE ? THEN 1 ELSE 2 END ASC, RAND()', ['%' . $keyword . '%']];
            $base       = fn () => Advert::where('id', '!=', $ad->id)->activeNotRecentlySold();

            if ($ad->brand) {
                $q = $base()
                    ->where('category', $ad->category)
                    ->where('sub_category', $ad->sub_category)
                    ->where('brand', $ad->brand);
                if ((clone $q)->count() > 0) {
                    return $q->orderByRaw(...$titleOrder);
                }
            }

            if ($ad->sub_category) {
                $q = $base()
                    ->where('category', $ad->category)
                    ->where('sub_category', $ad->sub_category);
                if ((clone $q)->count() > 0) {
                    return $q->orderByRaw(...$titleOrder);
                }
            }

            $q = $base()->where('category', $ad->category);
            if ((clone $q)->count() > 0) {
                return $q->orderByRaw(...$titleOrder);
            }

            return $base()->inRandomOrder();
        })();

        $total = (clone $baseQuery)->count();
        $ads   = $baseQuery->skip(($page - 1) * $perPage)->take($perPage)->get();

        $data = $ads->map(function ($advert) {
            return [
                'id'             => $advert->id,
                'ad_id'          => $advert->ad_id,
                'ad_title'       => $advert->ad_title,
                'price'          => $advert->price,
                'contact_price'  => $advert->contact_price,
                'price_type'     => $advert->price_type,
                'state'          => $advert->state,
                'state_slug'     => $advert->state_slug,
                'title_slug'     => $advert->title_slug,
                'category'       => $advert->category,
                'sub_category'   => $advert->sub_category,
                'brand_id'       => $advert->brand ?: null,
                'buy_direct'     => $advert->buy_direct,
                'featured'       => $advert->featured,
                'sold'           => $advert->sold,
                'views'          => $advert->views,
                'image_thumb'    => $advert->getFirstImageUrl('thumbnail'),
                'image_optimized'=> $advert->getFirstImageUrl('optimized'),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'total'        => $total,
                'current_page' => $page,
                'per_page'     => $perPage,
                'last_page'    => $perPage > 0 ? (int) ceil($total / $perPage) : 1,
                'has_more'     => $total > ($page * $perPage),
                'source_ad'    => [
                    'ad_id'        => $ad->ad_id,
                    'ad_title'     => $ad->ad_title,
                    'category'     => $ad->category,
                    'sub_category' => $ad->sub_category,
                    'brand_id'     => $ad->brand ?: null,
                ],
            ],
        ]);
    }

    /**
     * Get seller storefront — profile, ratings, followers and paginated ads
     * GET /api/adverts/seller/{seller_id}?page=1&per_page=20
     *
     * seller_id: the seller's unique user_id (5-char code)
     */
    public function sellerAdverts($seller_id, Request $request)
    {
        $owner = User::where('user_id', $seller_id)->first();

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found'
            ], 404);
        }

        $perPage = (int) $request->get('per_page', 20);

        $ads = Advert::with('firstImage', 'owner')
            ->where('user_id', $seller_id)
            ->activeNotRecentlySold()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $feedbackAverages = get_user_feedback_averages($owner->user_id);
        $feedbackLabels   = feedback_rating_labels($owner->user_id);

        return response()->json([
            'success' => true,
            'data' => [
                'seller' => [
                    'user_id'      => $owner->user_id,
                    'name'         => $owner->name,
                    'slug'         => \Illuminate\Support\Str::slug($owner->name),
                    'store_url'    => url('/seller/' . \Illuminate\Support\Str::slug($owner->name) . '/' . $owner->user_id),
                    'avatar_url'   => $owner->profile_thumbnail_url,
                    'acc_type'     => $owner->acc_type,
                    'verified'     => $owner->verified === 'yes',
                    'city'         => $owner->city,
                    'state'        => $owner->state,
                    'member_since' => $owner->created_at->format('F Y'),
                    'followers'    => countUserFollowers($owner->user_id),
                    'ads_count'    => Advert::where('user_id', $seller_id)->activeNotRecentlySold()->count(),
                    'feedback' => [
                        'review_count' => $feedbackAverages['count'] ?? 0,
                        'satisfaction' => $feedbackLabels['satisfaction'],
                        'friendly'     => $feedbackLabels['friendly'],
                        'reliable'     => $feedbackLabels['reliable'],
                    ],
                ],
                'ads' => $ads,
            ]
        ]);
    }

    /**
     * Get all categories with counts
     * GET /api/categories
     */
    public function categories()
    {
        $categoryCounts = Category::leftJoin('sub_categories as sc', 'categories.id', '=', 'sc.cat_id')
            ->leftJoin('adverts', function($join) {
                $join->on('sc.id', '=', 'adverts.sub_category')
                     ->where('adverts.ad_status', 'active')
                     ->where(function($q) {
                         $q->where('adverts.sold_date', '>=', now()->subDays(30))
                           ->orWhereNull('adverts.sold_date');
                     });
            })
            ->selectRaw('categories.id, categories.category AS category_name, categories.category_slug,
                        sc.id as sub_category_id, sc.sub_category AS sub_category_name, sc.sub_cat_slug,
                        COUNT(DISTINCT adverts.id) AS advert_count')
            ->groupBy('categories.id', 'categories.category', 'categories.category_slug',
                     'sc.id', 'sc.sub_category', 'sc.sub_cat_slug')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categoryCounts
        ]);
    }

    /**
     * Get adverts by category
     * GET /api/categories/{category_slug}
     */
    /**
     * Get subcategories for a category with ad counts and thumbnail images
     * GET /api/categories/{category_slug}/subcategories
     */
    public function categorySubcategories($category_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->first();

        if (!$cat) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $subcategories = DB::table('sub_categories')
            ->leftJoin('adverts', function ($join) {
                $join->on('sub_categories.id', '=', 'adverts.sub_category')
                    ->where('adverts.ad_status', 'active')
                    ->where(function ($q) {
                        $q->where('adverts.sold_date', '>=', now()->subDays(30))
                          ->orWhereNull('adverts.sold_date');
                    });
            })
            ->where('sub_categories.cat_id', $cat->id)
            ->select(
                'sub_categories.id',
                'sub_categories.sub_category',
                'sub_categories.sub_cat_slug',
                'sub_categories.icon',
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug', 'sub_categories.icon')
            ->orderBy('sub_categories.sub_category', 'asc')
            ->get();

        // One representative thumbnail per subcategory (2 queries total)
        $firstAdIds = Advert::activeNotRecentlySold()
            ->whereIn('sub_category', $subcategories->pluck('id'))
            ->select('sub_category', DB::raw('MIN(id) as first_ad_id'))
            ->groupBy('sub_category')
            ->pluck('first_ad_id', 'sub_category');

        $firstAds = Advert::with('firstImage')
            ->whereIn('id', $firstAdIds->values())
            ->get()
            ->keyBy('id');

        $subcatImages = [];
        foreach ($firstAdIds as $subcatId => $adId) {
            $ad = $firstAds->get($adId);
            if ($ad && $ad->firstImage) {
                $media = $ad->firstImage;
                $subcatImages[$subcatId] = $media->hasGeneratedConversion('thumbnail')
                    ? $media->getUrl('thumbnail')
                    : $media->getUrl();
            }
        }

        $total_ads = Advert::activeNotRecentlySold()->where('category', $cat->id)->count();

        $result = $subcategories->map(function ($subcat) use ($subcatImages) {
            return [
                'id'            => $subcat->id,
                'name'          => $subcat->sub_category,
                'slug'          => $subcat->sub_cat_slug,
                'advert_count'  => (int) $subcat->advert_count,
                'icon_url'      => $subcat->icon ? asset('frontend/images/subcategory-icons/' . $subcat->icon) : null,
                'thumbnail_url' => $subcatImages[$subcat->id] ?? null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'category' => [
                'id'         => $cat->id,
                'name'       => $cat->category,
                'slug'       => $cat->category_slug,
                'total_ads'  => $total_ads,
            ],
            'subcategories' => $result,
            'total'         => $result->count(),
        ]);
    }

    /**
     * Get adverts by category
     * GET /api/categories/{category_slug}
     */
    public function categoryAdverts($category_slug, Request $request)
    {
        $cat = Category::where('category_slug', $category_slug)->first();

        if (!$cat) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $page = $request->get('page', 1);
        $result = (new FeaturedAdPaginator($page))
            ->filters(['category' => $cat->id])
            ->get();

        $count_cat = Advert::activeNotRecentlySold()
                          ->where('category', $cat->id)
                          ->count();

        $subcategories = DB::table('sub_categories')
            ->leftJoin('adverts', function ($join) {
                $join->on('sub_categories.id', '=', 'adverts.sub_category')
                    ->where('adverts.ad_status', 'active')
                    ->where(function ($q) {
                        $q->where('adverts.sold_date', '>=', now()->subDays(30))
                          ->orWhereNull('adverts.sold_date');
                    });
            })
            ->where('sub_categories.cat_id', $cat->id)
            ->select(
                'sub_categories.id',
                'sub_categories.sub_category',
                'sub_categories.sub_cat_slug',
                'sub_categories.icon',
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug', 'sub_categories.icon')
            ->orderBy('advert_count', 'desc')
            ->get()
            ->map(function ($subcat) {
                $subcat->icon_url = $subcat->icon
                    ? asset('frontend/images/subcategory-icons/' . $subcat->icon)
                    : null;
                return $subcat;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'ads'           => $result['ads'],
                'cat'           => $cat,
                'count_cat'     => $count_cat,
                'subcategories' => $subcategories,
            ],
            'pagination' => [
                'has_more'     => $result['hasMore'],
                'next_page'    => $result['nextPage'],
                'current_page' => $result['current_page'],
                'per_page'     => $result['per_page'],
                'total'        => $result['total'],
                'last_page'    => $result['last_page'],
            ]
        ]);
    }

    /**
     * Get adverts by subcategory
     * GET /api/categories/{category_slug}/{subcat_slug}
     */
    public function subcategoryAdverts($category_slug, $subcat_slug, Request $request)
    {
        $cat = Category::where('category_slug', $category_slug)->first();

        if (!$cat) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)
                             ->where('cat_id', $cat->id)
                             ->first();

        if (!$subcat) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $page = $request->get('page', 1);
        $result = (new FeaturedAdPaginator($page))
            ->filters(['sub_category' => $subcat->id])
            ->get();

        $count_subcat = Advert::activeNotRecentlySold()
                            ->where('sub_category', $subcat->id)
                            ->count();

        $brands = DB::table('brands')
            ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
            ->where('brands.subcat_id', $subcat->id)
            ->where('adverts.ad_status', 'active')
            ->where(function($query) {
                $query->where('adverts.sold_date', '>=', now()->subDays(30))
                      ->orWhereNull('adverts.sold_date');
            })
            ->select('brands.id', 'brands.brand', 'brands.brand_slug', DB::raw('COUNT(adverts.id) as advert_count'))
            ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
            ->orderBy('advert_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ads'         => $result['ads'],
                'subcat'      => $subcat,
                'count_subcat'=> $count_subcat,
                'brands'      => $brands,
            ],
            'pagination' => [
                'has_more'     => $result['hasMore'],
                'next_page'    => $result['nextPage'],
                'current_page' => $result['current_page'],
                'per_page'     => $result['per_page'],
                'total'        => $result['total'],
                'last_page'    => $result['last_page'],
            ]
        ]);
    }

    /**
     * Get adverts by brand
     * GET /api/brands/{category_slug}/{subcat_slug}/{brand_slug}
     *
     * Optional ?model={id|slug} narrows to one model (same as the model
     * endpoint below). data.models is the "Shop by Model" list for the brand
     * (models with active listings, most listings first) — empty for brands
     * without models.
     */
    public function brandAdverts($category_slug, $subcat_slug, $brand_slug, Request $request)
    {
        return $this->brandListing($request, $category_slug, $subcat_slug, $brand_slug, $request->input('model'));
    }

    /**
     * Get adverts for one model of a brand, e.g. Apple → iPhone 12 Pro Max
     * GET /api/brands/{category_slug}/{subcat_slug}/{brand_slug}/{model_slug}
     */
    public function brandModelAdverts($category_slug, $subcat_slug, $brand_slug, $model_slug, Request $request)
    {
        return $this->brandListing($request, $category_slug, $subcat_slug, $brand_slug, $model_slug);
    }

    private function brandListing(Request $request, $category_slug, $subcat_slug, $brand_slug, $modelKey = null)
    {
        $cat = Category::where('category_slug', $category_slug)->first();

        if (!$cat) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)
                             ->where('cat_id', $cat->id)
                             ->first();

        if (!$subcat) {
            return response()->json([
                'success' => false,
                'message' => 'Subcategory not found'
            ], 404);
        }

        $brand = Brands::where('brand_slug', $brand_slug)
                       ->where('subcat_id', $subcat->id)
                       ->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $model = null;
        if ($modelKey !== null && $modelKey !== '') {
            $model = VehicleModel::where('brand_id', $brand->id)
                ->where(fn ($q) => $q->where('model_slug', $modelKey)
                    ->when(ctype_digit((string) $modelKey), fn ($q) => $q->orWhere('id', $modelKey)))
                ->first();

            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model not found'
                ], 404);
            }
        }

        $filters = ['brand' => $brand->id];
        if ($model) {
            $filters['model'] = $model->id;
        }

        $page = $request->get('page', 1);
        $result = (new FeaturedAdPaginator($page))
            ->filters($filters)
            ->get();

        $count_subcat = Advert::activeNotRecentlySold()
                        ->where('sub_category', $subcat->id)
                        ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'ads'          => $result['ads'],
                'brand'        => $brand,
                'model'        => $model,
                'models'       => (new AdvertQueryService())->getModelsForBrand($brand->id, $cat->id),
                'subcat'       => $subcat,
                'count_subcat' => $count_subcat,
            ],
            'pagination' => [
                'has_more'     => $result['hasMore'],
                'next_page'    => $result['nextPage'],
                'current_page' => $result['current_page'],
                'per_page'     => $result['per_page'],
                'total'        => $result['total'],
                'last_page'    => $result['last_page'],
            ]
        ]);
    }

    /**
     * Get adverts by location
     * GET /api/location/{state_slug}
     */
    public function locationAdverts($state_slug, Request $request)
    {
        $page = $request->get('page', 1);
        $result = (new FeaturedAdPaginator($page))
            ->filters(['state_slug' => $state_slug])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ads'      => $result['ads'],
                'location' => $state_slug,
            ],
            'pagination' => [
                'has_more'     => $result['hasMore'],
                'next_page'    => $result['nextPage'],
                'current_page' => $result['current_page'],
                'per_page'     => $result['per_page'],
                'total'        => $result['total'],
                'last_page'    => $result['last_page'],
            ]
        ]);
    }

    /**
     * Report an advert
     *
     * Submit a report for an inappropriate or suspicious advert.
     * The report is sent to the admin team for review.
     *
     * @group Adverts
     * @authenticated
     *
     * @urlParam id integer required The advert ID to report. Example: 123
     *
     * @bodyParam subject string required Report subject/category. Example: Spam or misleading content
     * @bodyParam message string required Detailed description of the issue. Example: This advert contains false information about the product.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Your report has been received"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Advert not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "errors": {
     *     "subject": ["The subject field is required."]
     *   }
     * }
     */
    public function reportAdvert($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $advert = Advert::with('owner')->find($id);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $user = auth()->user();

        Reports::updateOrCreate(
            [
                'advert_id' => $id,
                'user_id' => $user->user_id,
            ],
            [
                'subject' => $request->subject,
                'message' => $request->message,
                'reported_ad_title' => $advert->ad_title,
                'reported_ad_number' => $advert->ad_id,
                'reported_seller_id' => $advert->owner->user_id ?? null,
                'reported_seller_name' => $advert->owner->name ?? null,
                'reported_seller_phone' => $advert->owner->phone ?? null,
                'reported_seller_email' => $advert->owner->email ?? null,
            ]
        );

        $details = [
            'advert' => $advert->ad_title,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        try {
            Mail::to(config('global.admin_email'))->send(new ReportMail($details));
        } catch (\Exception $e) {
            // Log but don't fail the request
            \Log::error('Report email failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your report has been received'
        ]);
    }

    /**
     * Apply for a job advert
     * POST /api/adverts/{id}/apply
     */
    public function applyJob($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $advert = Advert::find($id);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $user = auth()->user();

        Message::create([
            'advert_id' => $id,
            'sender_id' => $user->id,
            'receiver_id' => $advert->user_id,
            'message_content' => $request->message,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You have successfully applied for the job'
        ]);
    }

    /**
     * Get featured adverts
     * GET /api/adverts/featured
     */
    public function featuredAdverts()
    {
        $featured = Advert::inRandomOrder()
            ->where('ad_status', 'active')
            ->where('featured', 'yes')
            ->where(function($query) {
                $query->where('sold', '!=', 'Yes')
                      ->orWhere(function($query) {
                          $query->where('sold', 'Yes')
                                ->whereNotNull('sold_date')
                                ->where('sold_date', '>=', now()->subDays(30));
                      });
            })
            ->with('firstImage', 'owner')
            ->orderBy('views', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featured
        ]);
    }

    /**
     * Load more adverts (pagination helper)
     * GET /api/adverts/load-more
     */
    public function loadMore(Request $request)
    {
        $page = $request->get('page', 1);
        $filters = $request->only(['category', 'sub_category', 'brand', 'model', 'state', 'state_slug', 'city']);

        $result = (new FeaturedAdPaginator($page))
            ->filters($filters)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ads' => $result['ads'],
            ],
            'pagination' => [
                'has_more'     => $result['hasMore'],
                'next_page'    => $result['nextPage'],
                'current_page' => $result['current_page'],
                'per_page'     => $result['per_page'],
                'total'        => $result['total'],
                'last_page'    => $result['last_page'],
            ]
        ]);
    }

    /**
     * Validate delivery details and return the server-side price breakdown.
     * POST /api/shipping/calculate/{advert_id}
     *
     * Courier integrations are off: the buyer supplies a delivery location
     * and the seller arranges delivery. Body: first_name, last_name, phone,
     * state (region id), lga (zone name), address.
     */
    public function calculate_shipping(Request $request, $id)
    {
        $user = $request->user();
        $ad = Advert::find($id);

        if ($error = BuyDirectCheckout::eligibilityError($ad, $user)) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        $validator = Validator::make($request->all(), BuyDirectCheckout::rules(), BuyDirectCheckout::messages());
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $input = $validator->validated();
        if ($lgaError = BuyDirectCheckout::lgaError((int) $input['state'], $input['lga'])) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => ['lga' => [$lgaError]]], 422);
        }

        $checkout = BuyDirectCheckout::build($ad, $input);

        return response()->json([
            'success' => true,
            'data' => [
                'ad' => [
                    'id' => $ad->id,
                    'title' => $ad->ad_title,
                    'price' => $ad->price,
                    'image_large' => $ad->getFirstImageUrl('large'),
                    'image_optimized' => $ad->getFirstImageUrl('optimized'),
                    'image_thumb' => $ad->getFirstImageUrl('thumbnail'),
                ],
                'item_price' => $checkout['item_price'],
                'commission_rate' => $checkout['commission_rate'],
                'commission' => $checkout['commission'],
                'shipping_cost' => 0,
                'grand_total' => $checkout['grand_total'],
                'delivery' => [
                    'first_name' => $checkout['first_name'],
                    'last_name' => $checkout['last_name'],
                    'phone' => $checkout['phone'],
                    'state' => ['id' => $checkout['state_id'], 'name' => $checkout['state_name']],
                    'lga' => $checkout['lga'],
                    'address' => $checkout['delivery_address'],
                ],
                'escrow' => [
                    'ship_deadline_days' => BuyDirectSettings::shipDeadlineDays(),
                    'auto_release_days' => BuyDirectSettings::autoReleaseDays(),
                ],
                // Send this object unchanged to POST /api/payments/initialize.
                'checkout' => [
                    'advert_id' => $ad->id,
                    'first_name' => $checkout['first_name'],
                    'last_name' => $checkout['last_name'],
                    'phone' => $checkout['phone'],
                    'state' => $checkout['state_id'],
                    'lga' => $checkout['lga'],
                    'address' => $checkout['delivery_address'],
                ],
            ]
        ]);
    }

    /**
     * Buy Direct checkout bootstrap: advert, price breakdown, regions, escrow terms.
     * GET /api/adverts/{advert_id}/buy-direct
     */
    public function buy_direct(Request $request, $id)
    {
        $user = $request->user();
        $ad = Advert::find($id);

        if ($error = BuyDirectCheckout::eligibilityError($ad, $user)) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ad' => [
                    'id' => $ad->id,
                    'ad_id' => $ad->ad_id,
                    'title' => $ad->ad_title,
                    'price' => $ad->price,
                    'state' => $ad->state,
                    'image_large' => $ad->getFirstImageUrl('large'),
                    'image_thumb' => $ad->getFirstImageUrl('thumbnail'),
                ],
                'quote' => BuyDirectSettings::quote((float) $ad->price),
                'prefill' => [
                    'first_name' => \Illuminate\Support\Str::before($user->name ?? '', ' '),
                    'last_name' => \Illuminate\Support\Str::after($user->name ?? '', ' '),
                    'phone' => $user->phone,
                ],
                'states' => State::orderBy('name')->get(['id', 'name']),
                'lgas_endpoint' => '/api/locations/states/{state_id}/lgas',
                'escrow' => [
                    'ship_deadline_days' => BuyDirectSettings::shipDeadlineDays(),
                    'auto_release_days' => BuyDirectSettings::autoReleaseDays(),
                ],
            ]
        ]);
    }

    /**
     * Price breakdown for an advert (no delivery details needed).
     * POST /api/adverts/{advert_id}/buy-direct-payment
     */
    public function buy_direct_payment(Request $request, $id)
    {
        $ad = Advert::find($id);

        if ($error = BuyDirectCheckout::eligibilityError($ad, $request->user())) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'advert_id' => $ad->id,
                'quote' => BuyDirectSettings::quote((float) $ad->price),
                'escrow' => [
                    'ship_deadline_days' => BuyDirectSettings::shipDeadlineDays(),
                    'auto_release_days' => BuyDirectSettings::autoReleaseDays(),
                ],
            ]
        ]);
    }

}
