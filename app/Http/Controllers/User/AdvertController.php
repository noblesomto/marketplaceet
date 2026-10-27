<?php

namespace App\Http\Controllers\User;
use App\Services\BuyDirectCheckout;
use App\Services\BuyDirectSettings;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Shop\LocationController;
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
use App\Models\Shipping;
use App\Models\GigLogistic;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rules\ReCaptcha;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\FeaturedAdPaginator;
use App\Services\AdvertQueryService;
use App\Services\FilterService;
use App\Services\SlugRedirectResolver;
use App\Support\AdvertVisibility;
use App\Mail\ReportMail;
use Mail;
use Jenssegers\Agent\Agent;
use App\Traits\HasUserSession;
use App\Traits\GeneratesSeoMeta;


class AdvertController extends Controller
{
    use HasUserSession, GeneratesSeoMeta;

    public function __construct(
        private AdvertQueryService $advertQueryService,
        private FilterService $filterService,
        private SlugRedirectResolver $slugRedirects,
    ) {}
    public function index(Request $request)
    {
        $title = config('global.site_name') . " | " . config('global.site_title');
        /*
        $gallery = Advert::inRandomOrder()
            //->where('featured', "Yes")
            ->where('sold', 'No')
            ->where('ad_status', 'active')
            ->whereHas('boost', function($query) {
                $query->where('boost_status', 'active');
            })
            ->with(['firstImage', 'boost' => function($query) {
                $query->where('boost_status', 'active');
            }])
            ->get()
            ->filter(function ($advert) {
                return $advert->boost->contains(function ($boost) {
                    return $boost->is_active;
                });
            })
            ->take(6) // Take only 6 after filtering
            ->values(); // Reindex collection
        */
        $agent = new Agent();
        if (request()->has('view')) {
            $isMobile = request()->get('view') === 'mobile';
        } else {
            $isMobile = $agent->isMobile() || $agent->isTablet();
        }

        // Configuration
        $perPage = 30;
        $featuredLimit = $isMobile ? 8 : 10;
        $currentPage = max(1, (int) request()->get('page', 1));

        // Get featured ads (only on first page)
        if ($currentPage == 1) {
            $featured = Advert::with('firstImage', 'owner')
                ->activeNotSold()
                ->where('featured', 'yes')
                ->inRandomOrder()
                ->limit($featuredLimit)
                ->get();

            $featuredCount = $featured->count();
            $remainingSlots = $perPage - $featuredCount;
        } else {
            $featured = collect();
            $featuredCount = 0;
            $remainingSlots = $perPage;
        }

        // Fetch regular listings to fill remaining slots
        $excludeIds = $featured->pluck('id')->toArray();

        // Split remaining slots: 40% recent, 60% random
        $recentCount = (int) ceil($remainingSlots * 0.4);
        $randomCount = $remainingSlots - $recentCount;

        // Calculate offset for pagination
        $recentOffset = $currentPage == 1 ? 0 : (($currentPage - 1) * $perPage) - $featuredCount;

        // Get recent listings
        $recentListings = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->whereNotIn('id', $excludeIds)
            ->where('featured', '!=', 'yes')
            ->orderByDesc('created_at')
            ->skip($recentOffset)
            ->limit($recentCount)
            ->get();

        // Update exclude IDs
        $excludeIds = array_merge($excludeIds, $recentListings->pluck('id')->toArray());

        // Get random listings
        $randomListings = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->whereNotIn('id', $excludeIds)
            ->where('featured', '!=', 'yes')
            ->inRandomOrder()
            ->limit($randomCount)
            ->get();

        // Interleave recent and random posts
        $mixed = collect();
        $maxCount = max($recentListings->count(), $randomListings->count());

        for ($i = 0; $i < $maxCount; $i++) {
            if ($recentListings->has($i)) {
                $mixed->push($recentListings->get($i));
            }
            if ($randomListings->has($i)) {
                $mixed->push($randomListings->get($i));
            }
        }

        // Combine featured with mixed listings
        $allListings = $currentPage == 1 ? $featured->concat($mixed) : $mixed;

        // Calculate total count for pagination
        $totalActiveListings = Advert::activeNotSold()
            ->where('featured', '!=', 'yes')
            ->count();

        $totalCount = $totalActiveListings + $featuredCount;

        // Create paginator
        $listings = new \Illuminate\Pagination\LengthAwarePaginator(
            $allListings,
            $totalCount,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Gallery
        $gallery = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // Category-specific listings
        $cars = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->where('sub_category', 2)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $phones = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->where('sub_category', 6)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $fashion = Advert::with('firstImage', 'owner')
            ->activeNotSold()
            ->where('category', 5)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // User and categories
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        //dd($categories);
        return view('public.index', compact('title','gallery','listings','featured','user','categories','cars','phones','fashion','isMobile'));
    }

    public function advert(Request $request, $location, $slug, $id)
    {
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $ad = Advert::with(['images', 'owner', 'firstImage'])
            ->where('ad_id', $id)
            ->first();

        // Not found, blocked, or hidden by admin — see App\Support\AdvertVisibility
        // (also used by the API so both surfaces agree on what "gone" means).
        if (AdvertVisibility::reasonUnavailable($ad)) {
            return redirect('/');
        }

        // Canonical slug/location guard — redirect to correct URL if slug or location drifted
        if ($slug !== $ad->title_slug || $location !== $ad->state_slug) {
            return redirect(url($ad->state_slug . '/' . $ad->title_slug . '/' . $id), 301);
        }

        $sessionKey = 'back_url_for_ad_' . $id;

        if (!session()->has($sessionKey)) {
            $referer = request()->headers->get('referer');
            // Only store if referrer exists and is from your own domain
            if ($referer && str_starts_with($referer, url('/'))) {
                // Don't store the current page as referrer (prevents loops)
                if ($referer !== request()->url()) {
                    session([$sessionKey => $referer]);
                }
            }
        }

        $data['ad'] = $ad;
        $ad_id = $ad->id;

        $data['title'] = $ad->ad_title.' - '.config('global.site_name');
        $title = $ad->ad_title;
        $cat_id = $ad->category;
        $brand_id = $ad->brand;
        $ad_owner = $ad->user_id;
        $subcat_id = $ad->sub_category;
        $user_id = $request->session()->get('user_id');

        // is_portrait detection removed — images are now in Spatie MediaLibrary,
        // not at uploads/images/, so File::exists() triggered open_basedir errors.

        $data['user'] = User::where('user_id', $user_id)->first();
        $data['ad_owner'] = User::where('user_id', $ad_owner)->first();
        $data['cat'] = Category::where('id', $cat_id)->first();
        $data['sub_cat'] = SubCategory::where('id', $subcat_id)->first();
        $data['brand'] = Brands::where('id', $brand_id)->first();

        $data['car'] = CarDetail::where('advert_id', $ad_id)->first();
        if ($data['car']) {
            $model_id = $data['car']->model;
            $data['model'] = VehicleModel::where('id', $model_id)->first();
        }

        $data['phone'] = PhoneDetail::where('advert_id', $ad_id)->first();
        if ($data['phone']) {
            $model_id = $data['phone']->model;
            $data['model'] = VehicleModel::where('id', $model_id)->first();
        }

        $data['computer'] = ComputerDetail::where('advert_id', $ad_id)->first();
        if ($data['computer']) {
            $data['model'] = VehicleModel::where('id', $data['computer']->model)->first();
        }

        $data['count_ads'] = Advert::where('user_id', $ad_owner)->activeNotRecentlySold()->count();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        $data['isMobile'] = $isMobile;

        /*
        |--------------------------------------------------------------------------
        | Related Adverts
        |--------------------------------------------------------------------------
        */
        $query = Advert::with(['images', 'owner', 'firstImage'])
            ->inRandomOrder()
            ->where('user_id', $ad_owner)
            ->activeNotRecentlySold()
            ->where('id', '!=', $ad_id);

        // 1. Set the maximum potential limit
        $limit = $isMobile ? 4 : 5;

        // 2. Fetch the adverts
        $adverts = $query->limit($limit)->get();

        // 3. Ensure the actual count is even for mobile
        // This handles the case where the DB returns 3 items even though the limit was 4.
        if ($isMobile && $adverts->count() % 2 !== 0) {
            $adverts = $adverts->slice(0, -1); // Remove the last item to make it even
        }

        $data['isMobile']     = $isMobile;
        $data['adverts']      = $adverts;
        $data['advertsCount'] = $adverts->count();

        /*
        |--------------------------------------------------------------------------
        | Similar Adverts
        |--------------------------------------------------------------------------
        */
        // Eager-load owner: each advert card shows the seller's verified badge.
        $data['similar_ads'] = Advert::with(['images', 'owner', 'firstImage'])
            ->inRandomOrder()
            ->where(function ($q) use ($title, $cat_id) {
                $q->where('ad_title', 'LIKE', '%' . $title . '%')
                  ->orWhere('category', $cat_id);
            })
            ->where('id', '!=', $ad_id)
            ->activeNotRecentlySold()
            ->where('user_id', '!=', $ad_owner)
            ->limit($isMobile ? 20 : 25)
            ->get();


        \DB::table('adverts')
            ->where('id', $ad_id)
            ->increment('views', 1);

        if ($user) {
            $notification = Notification::where('user_id', $user->id)
                ->where('advert_id', $ad_id)
                ->first();

            if ($notification) {
                $notification->update(['is_read' => true]);
            }
        }


        return view('public.advert', $data);
    }

    public function loadMoreAds(Request $request)
{


    $perPage = 20;
    $currentPage = max(1, (int) $request->get('page', 1));

    // If it's page 1, get featured posts
    if ($currentPage == 1) {
        $featured = Advert::with('firstImage', 'owner')
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
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $excludeIds = $featured->pluck('id')->toArray();
    } else {
        $featured = collect();
        $excludeIds = [];
    }

    // Split: 30% recent, 70% random older posts
    $recentCount = (int) ($perPage * 0.3);
    $randomCount = $perPage - $recentCount;

    // Get recent listings (excluding featured)
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

    // Get random older listings
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

    // Interleave recent and random posts
    $mixed = collect();
    $maxCount = max($recentListings->count(), $randomListings->count());

    for ($i = 0; $i < $maxCount; $i++) {
        if ($recentListings->has($i)) {
            $mixed->push($recentListings->get($i));
        }
        if ($randomListings->has($i)) {
            $mixed->push($randomListings->get($i));
        }
    }

    // On first page, prepend featured posts
    if ($currentPage == 1) {
        $ads = $featured->concat($mixed);
    } else {
        $ads = $mixed;
    }

    // Generate HTML
    $html = '';
    foreach ($ads as $row) {
        $html .= view('public.components.advert.advert-card', compact('row'))->render();
    }

    // Check if there are more pages
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

    $hasMorePages = ($currentPage * $perPage) < $totalCount;


    // ✅ FIX: Use snake_case to match JavaScript
    return response()->json([
        'html' => $html,
        'next_page' => $hasMorePages ? $currentPage + 1 : null,  // ← Changed from 'nextPage'
    ]);
}


    public function loadMoreAdsMobile(Request $request)
    {

        $perPage = 20;
        $currentPage = max(1, (int) $request->get('page', 1));

        // If it's page 1, get featured posts
        if ($currentPage == 1) {
            $featured = Advert::with('firstImage', 'owner')
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
                ->inRandomOrder()
                ->limit(10)
                ->get();

            $excludeIds = $featured->pluck('id')->toArray();
        } else {
            $featured = collect();
            $excludeIds = [];
        }

        // Split: 50% recent, 50% random older posts
        $recentCount = (int) ($perPage * 0.3); // 10 posts
        $randomCount = $perPage - $recentCount; // 10 posts

        // Get recent listings (excluding featured)
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

        // Get random older listings
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

        // Interleave recent and random posts
        $mixed = collect();
        $maxCount = max($recentListings->count(), $randomListings->count());

        for ($i = 0; $i < $maxCount; $i++) {
            if ($recentListings->has($i)) {
                $mixed->push($recentListings->get($i));
            }
            if ($randomListings->has($i)) {
                $mixed->push($randomListings->get($i));
            }
        }

        // On first page, prepend featured posts
        if ($currentPage == 1) {
            $ads = $featured->concat($mixed);
        } else {
            $ads = $mixed;
        }

        // Generate HTML
        $html = '';
        foreach ($ads as $row) {
            $html .= view('public.components.advert.advert-card-mobile', compact('row'))->render();
        }

        // Check if there are more pages
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

        $hasMorePages = ($currentPage * $perPage) < $totalCount;

        return response()->json([
            'html' => $html,
            'next_page' => $hasMorePages ? $currentPage + 1 : null,
        ]);
    }


    // REMOVED: chat() method - duplicate functionality, replaced by MessageController::showMessages()
    // REMOVED: buildRelatedQuery() — moved to App\Services\AdvertQueryService

    public function related(Request $request, $ad_id)
    {
        $ad = Advert::with('firstImage', 'owner')->where('ad_id', $ad_id)->firstOrFail();

        $cat = Category::find($ad->category);
        if (!$cat) {
            return redirect('/');
        }

        $subcat = SubCategory::find($ad->sub_category);

        // First 2 words of the title for keyword matching
        $titleWords = array_filter(explode(' ', $ad->ad_title));
        $keyword    = implode(' ', array_slice(array_values($titleWords), 0, 2));

        $agent    = new Agent();
        $isMobile = $request->has('view')
            ? $request->get('view') === 'mobile'
            : ($agent->isMobile() || $agent->isTablet());

        $perPage   = 20;
        $page      = max(1, (int) $request->get('page', 1));
        $baseQuery = $this->advertQueryService->buildRelatedQuery($ad, $keyword);

        $total   = (clone $baseQuery)->count();
        $ads     = $baseQuery->skip(($page - 1) * $perPage)->take($perPage)->get();
        $hasMore = $total > ($page * $perPage);

        // Sidebar: subcategory list for the parent category
        $count_cat  = Advert::activeNotRecentlySold()->where('category', $cat->id)->count();
        $categories = DB::table('sub_categories')
            ->leftJoin('adverts', function ($join) {
                $join->on('sub_categories.id', '=', 'adverts.sub_category')
                    ->where('adverts.ad_status', 1)
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
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug')
            ->orderBy('advert_count', 'desc')
            ->get();

        $user_id = $request->session()->get('user_id');
        $user    = User::where('user_id', $user_id)->first();
        $title   = config('global.site_name') . ' | Related: ' . $ad->ad_title;
        $metaRobots = 'noindex, follow';

        return view('public.related', compact(
            'title', 'ads', 'user', 'categories', 'cat', 'count_cat',
            'hasMore', 'isMobile', 'ad', 'subcat', 'metaRobots'
        ));
    }

    public function relatedLoadMore(Request $request, $ad_id)
    {
        $ad = Advert::where('ad_id', $ad_id)->firstOrFail();

        $titleWords = array_filter(explode(' ', $ad->ad_title));
        $keyword    = implode(' ', array_slice(array_values($titleWords), 0, 2));

        $agent    = new Agent();
        $isMobile = $agent->isMobile() || $agent->isTablet();
        $perPage  = 20;
        $page     = max(1, (int) $request->get('page', 2));

        $baseQuery = $this->advertQueryService->buildRelatedQuery($ad, $keyword);
        $total     = (clone $baseQuery)->count();
        $ads       = $baseQuery->skip(($page - 1) * $perPage)->take($perPage)->get();

        $html = '';
        foreach ($ads as $row) {
            $view = $isMobile
                ? 'public.components.advert.advert-card-mobile'
                : 'public.components.advert.advert-card';
            $html .= view($view, compact('row'))->render();
        }

        return response()->json([
            'html'    => $html,
            'hasMore' => $total > ($page * $perPage),
        ]);
    }

    public function adverts(Request $request)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $page = max(1, (int) request()->get('page', 1));
        $perPage = 20;
        $featuredLimit = 6;

        // Step 1: Fetch top 6 featured boosted ads (not paginated)
        $featured = Advert::with(['firstImage', 'boost' => fn($q) => $q->where('boost_status', 'active')])
            ->featuredBoosted()
            ->inRandomOrder()
            ->limit($featuredLimit)
            ->get();

        // Step 2: Calculate how many regular ads are needed for this page
        $offset = max(0, ($page - 1) * $perPage - $featuredLimit);
        $regularLimit = $perPage - ($page == 1 ? $featured->count() : 0);

        // Step 3: Fetch regular ads
        $regular = Advert::with('firstImage')
            ->regularAds()
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($regularLimit)
            ->get();

        // Step 4: Merge for page 1, use regular only for later pages
        $ads = $page == 1
            ? $featured->merge($regular)
            : $regular;

        // Step 5: Total count for pagination (add featured only to first page count)
        $total = Advert::regularAds()->count() + ($page == 1 ? $featured->count() : 0);

        // Step 6: Custom paginator
        $paginated = new LengthAwarePaginator(
            $ads,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $agent = new Agent();
        $isMobile = $agent->isMobile();

        //dd($categories);
        return view('public.adverts', compact('title', 'ads', 'user', 'categories', 'isMobile'));
    }

   public function seller(Request $request, $name, $id)
    {
        // Check if owner exists
        $owner = User::where('user_id', $id)->first();
        if (!$owner) {
            return redirect('/');
        }


        // Fetch seller's other ads
        $ads = Advert::with('firstImage', 'owner')
            ->where('user_id', $id)
            ->activeNotRecentlySold()
            ->orderBy('created_at', 'desc')
            ->paginate(20);


        $hasMore = $ads->hasMorePages(); // Add this line

        $title = config('global.site_name') . ' | ' . config('global.site_title');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();
        $count_ads = Advert::where('user_id', $id)->activeNotRecentlySold()->count();

        $following_count = \App\Models\Followers::where('user_id', $id)->count();
        return view('public.seller-adverts', compact('title', 'ads', 'user', 'owner', 'categories', 'count_ads', 'hasMore', 'following_count'));
    }

    public function loadMoreSellerAds(Request $request, $name, $id)
    {
        $owner = User::where('user_id', $id)->first();
        if (!$owner) {
            return response()->json(['error' => 'Seller not found'], 404);
        }

        $ads = Advert::with('firstImage', 'owner')
            ->where('user_id', $id)
            ->activeNotRecentlySold()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'html' => view('public.components.advert.advert-list', ['ads' => $ads])->render(),
            'hasMore' => $ads->hasMorePages()
        ]);
    }
   

    public function all_categories(Request $request)
    {
        $title = config('global.site_name').' | '.config('global.site_title');

        // Featured ads with scope
        $ads = Advert::with('firstImage')
                    ->activeNotRecentlySold()
                    ->orderBy('created_at', 'asc')
                    ->limit(10)
                    ->get();

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $categories = Category::with('subCategories')->get();

        // Category counts with filtering
        $categoryCounts = Category::leftJoin('adverts', function($join) {
                $join->on('categories.id', '=', 'adverts.category')
                     ->where('adverts.ad_status', 1)
                     ->where(function($q) {
                         $q->where('adverts.sold_date', '>=', now()->subDays(30))
                           ->orWhereNull('adverts.sold_date');
                     });
            })
            ->leftJoin('sub_categories as sc', function($join) {
                $join->on('adverts.sub_category', '=', 'sc.id')
                    ->orOn('categories.id', '=', 'sc.cat_id');
            })
            ->selectRaw('categories.id, categories.category AS category_name, categories.category_slug,
                        sc.id as sub_category_id, sc.sub_category AS sub_category_name, sc.sub_cat_slug,
                        COUNT(DISTINCT adverts.id) AS advert_count')
            ->groupBy('categories.id', 'categories.category', 'categories.category_slug',
                     'sc.id', 'sc.sub_category', 'sc.sub_cat_slug')
            ->get();

        return view('public.all-categories', compact('title','ads','user','categories','categoryCounts'));
    }

    public function loadMoreAdverts(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $filters = $request->only(['category', 'sub_category', 'brand', 'model', 'state', 'state_slug', 'city']);

        $result = (new FeaturedAdPaginator($page))
            ->filters($filters)
            ->get();

        return response()->json([
            'html'     => $this->filterService->renderAdvertCards($result['ads']),
            'hasMore'  => $result['hasMore'],
            'nextPage' => $result['nextPage'],
        ]);
    }

    public function mainCategory(Request $request, $category_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();

        $count_cat = Advert::activeNotRecentlySold()
            ->where('category', $cat->id)
            ->count();

        $seo   = $this->buildSeoMeta($cat->category, $cat->seo_group ?? 'product', url("/category/{$cat->category_slug}"), $request->get('location', 'Ethiopia'), $count_cat);
        $title = $seo['seoTitle'];

        $result = (new FeaturedAdPaginator(1))
            ->filters(['category' => $cat->id])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $categories = DB::table('sub_categories')
            ->leftJoin('adverts', function ($join) {
                $join->on('sub_categories.id', '=', 'adverts.sub_category')
                    ->where('adverts.ad_status', 1)
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

        // One representative thumbnail per subcategory for mobile list (2 queries)
        $firstAdIds = Advert::activeNotRecentlySold()
            ->whereIn('sub_category', $categories->pluck('id'))
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

        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($count_cat);

        return view('public.main-category', array_merge(compact(
            'title', 'ads', 'user', 'categories', 'cat', 'count_cat', 'hasMore', 'subcatImages', 'isMobile', 'metaRobots'
        ), $seo));
    }


        public function category(Request $request, $category_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $seo   = $this->buildSeoMeta($cat->category, $cat->seo_group ?? 'product', url("/category/{$cat->category_slug}"), $request->get('location', 'Ethiopia'));
        $title = $seo['seoTitle'];

        $result = (new FeaturedAdPaginator(1))
            ->filters(['category' => $cat->id])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $count_cat = Advert::activeNotRecentlySold()
            ->where('category', $cat->id)
            ->count();

        $categories = DB::table('sub_categories')
            ->leftJoin('adverts', function ($join) {
                $join->on('sub_categories.id', '=', 'adverts.sub_category')
                    ->where('adverts.ad_status', 1)
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
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug')
            ->orderBy('advert_count', 'desc')
            ->get();

            $filterType = 'category';
            $filterId = $cat->id;
            $filterIsString = false;

            $agent = new Agent();
            $isMobile = $agent->isMobile();

        return view('public.category', array_merge(compact('title', 'ads', 'user', 'categories', 'cat', 'count_cat', 'hasMore', 'isMobile'), $seo));
    }

    public function sub_category(Request $request, $category_slug, $subcat_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->first();

        if (!$subcat) {
            $newSlug = $this->slugRedirects->resolve('subcategory', $subcat_slug);
            $redirectTarget = $newSlug
                ? SubCategory::where('sub_cat_slug', $newSlug)->where('cat_id', $cat->id)->first()
                : null;

            if ($redirectTarget) {
                return redirect("/category/{$category_slug}/{$newSlug}", 301);
            }

            abort(404);
        }

        $count_subcat = Advert::activeNotRecentlySold()
            ->where('sub_category', $subcat->id)
            ->count();

        $seo   = $this->buildSeoMeta($subcat->sub_category, $cat->seo_group ?? 'product', url("/category/{$cat->category_slug}/{$subcat->sub_cat_slug}"), $request->get('location', 'Ethiopia'), $count_subcat);
        $title = $seo['seoTitle'];

        $result = (new FeaturedAdPaginator(1))
            ->filters(['sub_category' => $subcat->id])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $brands = $this->advertQueryService->getBrandsForSubcat($subcat->id);

        // IMPORTANT: Set these variables for the JavaScript
        $filterType = 'sub_category';
        $filterId = $subcat->id;
        $filterIsString = false;

        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($count_subcat);
        return view('public.sub-category', array_merge(compact('title', 'ads', 'user', 'brands', 'cat', 'subcat', 'count_subcat', 'hasMore', 'filterType', 'filterId', 'isMobile', 'metaRobots'), $seo));
    }

    public function brand(Request $request, $category_slug, $subcat_slug, $brand_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->first();

        if (!$subcat) {
            $newSubcatSlug = $this->slugRedirects->resolve('subcategory', $subcat_slug);
            $redirectSubcat = $newSubcatSlug
                ? SubCategory::where('sub_cat_slug', $newSubcatSlug)->where('cat_id', $cat->id)->first()
                : null;

            if ($redirectSubcat) {
                return redirect("/category/{$category_slug}/{$newSubcatSlug}/{$brand_slug}", 301);
            }

            abort(404);
        }

        $brand = Brands::where('brand_slug', $brand_slug)->where('subcat_id', $subcat->id)->first();

        if (!$brand) {
            $newBrandSlug = $this->slugRedirects->resolve('brand', $brand_slug);
            $redirectBrand = $newBrandSlug
                ? Brands::where('brand_slug', $newBrandSlug)->where('subcat_id', $subcat->id)->first()
                : null;

            if ($redirectBrand) {
                return redirect("/category/{$category_slug}/{$subcat_slug}/{$newBrandSlug}", 301);
            }

            // A retired brand that stood for the whole subcategory (Laptops & Computers → "Laptop")
            if ($newBrandSlug === $subcat->sub_cat_slug) {
                return redirect("/category/{$category_slug}/{$subcat_slug}", 301);
            }

            abort(404);
        }

        return $this->renderBrandPage($request, $cat, $subcat, $brand);
    }

    /**
     * /category/{cat}/{subcat}/{brand}/{model} — e.g. Apple → iPhone 12 Pro Max.
     * Same page as the brand, narrowed to the model; the brand page's
     * "Shop by Model" sidebar links here.
     */
    public function brandModel(Request $request, $category_slug, $subcat_slug, $brand_slug, $model_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();
        $brand = Brands::where('brand_slug', $brand_slug)->where('subcat_id', $subcat->id)->firstOrFail();

        $model = VehicleModel::where('model_slug', $model_slug)->where('brand_id', $brand->id)->first();

        if (!$model) {
            $newModelSlug = $this->slugRedirects->resolve('model', $model_slug);
            $redirectModel = $newModelSlug
                ? VehicleModel::where('model_slug', $newModelSlug)->where('brand_id', $brand->id)->first()
                : null;

            if ($redirectModel) {
                return redirect("/category/{$category_slug}/{$subcat_slug}/{$brand_slug}/{$newModelSlug}", 301);
            }

            abort(404);
        }

        return $this->renderBrandPage($request, $cat, $subcat, $brand, $model);
    }

    private function renderBrandPage(Request $request, Category $cat, SubCategory $subcat, Brands $brand, ?VehicleModel $model = null)
    {
        $filters = ['brand' => $brand->id];
        if ($model) {
            $filters['model'] = $model->id;
        }

        $result = (new FeaturedAdPaginator(1))
            ->filters($filters)
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $brandUrl = "/category/{$cat->category_slug}/{$subcat->sub_cat_slug}/{$brand->brand_slug}";
        $seoName = $model
            // "Xiaomi 13T Pro" already names the brand; "Galaxy S21" doesn't.
            ? (stripos($model->model, $brand->brand) === 0 ? $model->model : $brand->brand . ' ' . $model->model)
            : $brand->brand . ' ' . $subcat->sub_category;
        $canonical = url($model ? "{$brandUrl}/{$model->model_slug}" : $brandUrl);

        $seo   = $this->buildSeoMeta($seoName, $cat->seo_group ?? 'product', $canonical, $request->get('location', 'Ethiopia'), $result['total']);
        $title = $seo['seoTitle'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $count_subcat = Advert::activeNotRecentlySold()
            ->where('sub_category', $subcat->id)
            ->count();

        $brands = $this->advertQueryService->getBrandsForSubcat($subcat->id);
        $models = $this->advertQueryService->getModelsForBrand($brand->id, $cat->id);

            $filterType = 'brand';
            $filterId = $brand->id;
            $filterIsString = false;
            $agent = new Agent();
            $isMobile = $agent->isMobile();
            $metaRobots = $this->seoRobotsForCount($result['total']);

        return view('public.brand', array_merge(compact('title', 'ads', 'user', 'brand', 'brands', 'cat', 'subcat', 'count_subcat', 'hasMore', 'filterType', 'filterId', 'isMobile', 'metaRobots', 'models', 'model', 'brandUrl'), $seo));
    }

        public function location($location)
    {
        $result = (new FeaturedAdPaginator(1))
            ->filters(['state_slug' => $location])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $seo   = $this->buildLocationOnlySeoMeta($location, url("/{$location}"), $result['total']);
        $title = $seo['seoTitle'];

        $filterType = 'state_slug';
        $filterId = $location; // Just the raw value
        $filterIsString = true; // Flag to tell JS it's a string

        $categories = Category::with('subCategories')->get();
        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $metaRobots = $this->seoRobotsForCount($result['total']);

        return view('public.location', array_merge(compact('title','location', 'ads','categories', 'hasMore', 'filterType', 'filterId', 'filterIsString','isMobile', 'metaRobots'), $seo));
    }

    public function loadMoreLocation(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $filters = $request->only(['category', 'sub_category', 'brand', 'model', 'state', 'state_slug', 'city']);

        $result = (new FeaturedAdPaginator($page))
            ->filters($filters)
            ->get();

        $agent = new Agent();
        $html = '';

        if ($agent->isMobile()) {
            foreach ($result['ads'] as $row) {
                $html .= view('public.components.advert.advert-location-mobile', compact('row'))->render();
            }
        } else {
            foreach ($result['ads'] as $row) {
                $html .= view('public.components.advert.advert-location', compact('row'))->render();
            }
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $result['hasMore'],
            'nextPage' => $result['nextPage']
        ]);
    }

        public function all_subcat(Request $request, $category_slug, $subcat_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $subcat = SubCategory::where('sub_cat_slug', $subcat_slug)->where('cat_id', $cat->id)->firstOrFail();

        $title = config('global.site_name') . ' | ' . $subcat->sub_category . ' - All Brands';

        // CHANGED: Use .get() instead of .paginate()
        $result = (new FeaturedAdPaginator(1))
            ->filters(['sub_category' => $subcat->id])
            ->get();

        $ads = $result['ads'];
        $hasMore = $result['hasMore'];

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $count_subcat = Advert::activeNotRecentlySold()
            ->where('sub_category', $subcat->id)
            ->count();

        $brands = $this->advertQueryService->getBrandsForSubcat($subcat->id);

        // CHANGED: Added $hasMore to compact
        return view('public.all-subcat', compact('title','ads','user','cat','brands','subcat','count_subcat','subcat_slug', 'hasMore'));
    }



    public function all_category(Request $request, $category_slug)
    {
        $cat = Category::where('category_slug', $category_slug)->firstOrFail();
        $seo   = $this->buildSeoMeta($cat->category, $cat->seo_group ?? 'product', url("/category/all-{$cat->category_slug}"), $request->get('location', 'Ethiopia'));
        $title = $seo['seoTitle'];

        $result = (new FeaturedAdPaginator(1))
            ->filters(['category' => $cat->id])
            ->get();

        $ads     = $result['ads'];
        $hasMore = $result['hasMore'];

        $user_id = $request->session()->get('user_id');
        $user    = User::where('user_id', $user_id)->first();

        $count_cat = Advert::activeNotRecentlySold()
            ->where('category', $cat->id)
            ->count();

        $categories = DB::table('sub_categories')
            ->leftJoin('adverts', function ($join) {
                $join->on('sub_categories.id', '=', 'adverts.sub_category')
                    ->where('adverts.ad_status', 1)
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
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug')
            ->orderBy('advert_count', 'desc')
            ->get();

        $agent    = new Agent();
        $isMobile = $agent->isMobile();

        return view('public.category', array_merge(compact('title', 'ads', 'user', 'categories', 'cat', 'count_cat', 'hasMore', 'isMobile'), $seo));
    }

    public function mobile_category(Request $request, $id, $slug)
    {
        $title = config('global.site_name').' | '.config('global.site_title');
        $ads = Advert::with('firstImage')->where('category', $id)->orderBy('created_at', 'asc')->paginate(20);
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $cat = Category::where('id', $id)->first();
        $count_cat = Advert::where('category', $id)->count();
        $categories = DB::table('sub_categories')
            ->leftJoin('adverts', 'sub_categories.id', '=', 'adverts.sub_category')
            ->where('sub_categories.cat_id', $id)
            ->select('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug', DB::raw('COUNT(adverts.id) as advert_count'))
            ->groupBy('sub_categories.id', 'sub_categories.sub_category', 'sub_categories.sub_cat_slug')
            ->orderBy('advert_count', 'desc') // Optional: Order by count if needed
            ->get();

        //dd($categories);
        return view('public.mobile-category', compact('title', 'ads', 'user', 'categories', 'cat', 'count_cat'));
    }


    public function buy_direct(Request $request, $id)
    {
        $data['ad'] = $ad = Advert::with('images')->where('ad_id', $id)->first();

        if (!$ad) {
            abort(404, 'Advert not found.');
        }

        $data['title'] = $ad->ad_title.' - '.config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $data['user'] = $user = User::where('user_id', $user_id)->first();

        if (empty($user)) {
            $request->session()->forget('user_id');
            $request->session()->put('url.intended', url()->current());
            return redirect('/login')->with('error','Sorry, you need to login to Use Buy Direct');
        }

        if ($error = BuyDirectCheckout::eligibilityError($ad, $user)) {
            return redirect()->back()->with('error', $error);
        }

        $data['states'] = State::orderBy('name')->get();
        $data['quote'] = BuyDirectSettings::quote((float) $ad->price);
        $data['shipDeadlineDays'] = BuyDirectSettings::shipDeadlineDays();
        $data['autoReleaseDays'] = BuyDirectSettings::autoReleaseDays();
        $agent = new Agent();
        $data['isMobile'] = $agent->isMobile() || $agent->isTablet();

        return view('public.buy-direct', $data);
    }


    public function calculate_shipping(Request $request, $id)
    {
        $ad = Advert::find($id);
        $user = User::where('user_id', $request->session()->get('user_id'))->first();

        if ($error = BuyDirectCheckout::eligibilityError($ad, $user)) {
            return back()->with('error', $error);
        }

        $validated = $request->validate(BuyDirectCheckout::rules(), BuyDirectCheckout::messages());

        if ($lgaError = BuyDirectCheckout::lgaError((int) $validated['state'], $validated['lga'])) {
            return back()->withErrors(['lga' => $lgaError])->withInput();
        }

        $request->session()->put('buy_direct_checkout', BuyDirectCheckout::build($ad, $validated));

        return redirect()->route('buy.direct.payment', ['id' => $ad->id]);
    }

    public function buy_direct_payment(Request $request, $id)
    {
        $data['ad'] = $ad = Advert::with('images')->where('id', $id)->first();
        $user_id = $request->session()->get('user_id');
        $data['user'] = $user = User::where('user_id', $user_id)->first();

        $checkout = $request->session()->get('buy_direct_checkout');
        if (!$ad || !$checkout || (int) $checkout['advert_id'] !== (int) $ad->id) {
            return redirect($ad ? route('buy.direct', $ad->ad_id) : '/')->with('error', 'Please enter your delivery details first.');
        }

        if ($error = BuyDirectCheckout::eligibilityError($ad, $user)) {
            return redirect()->back()->with('error', $error);
        }

        // Re-price in case the ad price or commission settings changed since the form was submitted.
        $checkout = array_merge($checkout, BuyDirectSettings::quote((float) $ad->price));
        $request->session()->put('buy_direct_checkout', $checkout);

        $data['checkout'] = $checkout;
        $data['title'] = $ad->ad_title.' - '.config('global.site_name');
        $data['shipDeadlineDays'] = BuyDirectSettings::shipDeadlineDays();
        $data['autoReleaseDays'] = BuyDirectSettings::autoReleaseDays();
        $agent = new Agent();
        $data['isMobile'] = $agent->isMobile() || $agent->isTablet();

        return view('public.buy-direct-payment', $data);
    }

    public function report_advert(Request $request, $id)
    {
        $data['title'] = 'Report Advert | '.config('global.site_name');
        $data['ad'] = $advert = Advert::with(['images', 'owner', 'firstImage'])->where('id', $id)->first();
        $user_id = $request->session()->get('user_id');
        $data['user'] = $user = User::where('user_id', $user_id)->first();


        if (!$advert) {

            abort(404, 'Advert not found');
        }

        if ($request->isMethod('GET')) {
            return view('public.report-ad', $data);
        }


        if ($request->isMethod('POST')) {
            // Build validation rules
            $rules = [
                'name' => 'required',
                'phone' => 'required',
                'subject' => 'required',
                'message' => 'required',
            ];

            // Add reCAPTCHA validation only if enabled
            if (config('services.recaptcha.enabled', false)) {
                $rules['g-recaptcha-response'] = ['required', new ReCaptcha];
            }

            $request->validate($rules);

            $message = Reports::updateOrCreate(
                [
                    'advert_id' => $id,
                    'user_id' => $user_id,
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

            Mail::to(config('global.admin_email'))->queue(new ReportMail($details));

            return redirect()->back()->with('success', 'Your Report Has Been Received, We will Get back to Shortly');
        }
    }

    public function apply_job(Request $request, $id)
    {
        $data['title'] = 'Report Advert | '.config('global.site_name');
        $data['ad'] = Advert::with('images')->where('id', $id)->first();
        $user_id = $request->session()->get('user_id');
        $data['user'] = $user = User::where('user_id', $user_id)->first();
        $reciever = $data['ad']['owner']['user_id'];

        if ($request->isMethod('POST')) {
            $request->validate([
                'message' => 'required',
            ]);
            //dd($reciever);

            $message = Message::create([
                'advert_id' => $id,
                'sender_id' => $user_id,
                'receiver_id' => $reciever,
                'message_content' => $request->message,
                'is_read' => false,
            ]);

            return redirect()->back()->with('success', 'You Have successfuly Apllied for Job, The provider will Get back to Shortly');

        }
    }

    public function sellerFollowers($slug, $id)
    {
        $owner = \App\Models\User::where('user_id', $id)->firstOrFail();
        $loggedInUserId = session()->get('user_id');
        $user    = $loggedInUserId ? \App\Models\User::where('user_id', $loggedInUserId)->first() : null;
        $isOwner  = $user && $owner->user_id == $user->user_id;
        $loggedIn = (bool) $loggedInUserId;

        $rows = \App\Models\Followers::where('follow', $id)
            ->with(['user:user_id,name,state,city'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $myFollowingIds = $loggedInUserId
            ? \App\Models\Followers::where('user_id', $loggedInUserId)->pluck('follow')->toArray()
            : [];

        $people = $rows->getCollection()->map(function ($f) use ($myFollowingIds, $loggedInUserId) {
            $u = $f->user;
            if (!$u) return null;
            $name  = $u->name ?? 'User';
            $words = preg_split('/\s+/', trim($name));
            $initials = count($words) >= 2
                ? strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1))
                : strtoupper(mb_substr($name, 0, 2));
            return (object)[
                'user_id'      => $u->user_id,
                'name'         => $name,
                'initials'     => $initials,
                'state'        => trim(implode(', ', array_filter([$u->city, $u->state]))),
                'active_ads'   => Advert::where('user_id', $u->user_id)->activeNotRecentlySold()->count(),
                'is_following' => in_array($u->user_id, $myFollowingIds),
                'is_self'      => $loggedInUserId == $u->user_id,
                'profile_url'  => '/seller/' . \Illuminate\Support\Str::slug($name) . '/' . $u->user_id,
            ];
        })->filter()->values();

        return view('public.seller-social', [
            'title'           => $owner->name . "'s Followers — " . config('global.site_name'),
            'owner'           => $owner,
            'user'            => $user,
            'isOwner'         => $isOwner,
            'loggedIn'        => $loggedIn,
            'people'          => $people,
            'paginator'       => $rows,
            'tab'             => 'followers',
            'followers_count' => \App\Models\Followers::where('follow', $id)->count(),
            'following_count' => \App\Models\Followers::where('user_id', $id)->count(),
            'sellerSlug'      => $slug,
        ]);
    }

    public function sellerFollowing($slug, $id)
    {
        $owner = \App\Models\User::where('user_id', $id)->firstOrFail();
        $loggedInUserId = session()->get('user_id');
        $user    = $loggedInUserId ? \App\Models\User::where('user_id', $loggedInUserId)->first() : null;
        $isOwner  = $user && $owner->user_id == $user->user_id;
        $loggedIn = (bool) $loggedInUserId;

        $rows = \App\Models\Followers::where('user_id', $id)
            ->with(['seller:user_id,name,state,city'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $myFollowingIds = $loggedInUserId
            ? \App\Models\Followers::where('user_id', $loggedInUserId)->pluck('follow')->toArray()
            : [];

        $people = $rows->getCollection()->map(function ($f) use ($myFollowingIds, $loggedInUserId) {
            $u = $f->seller;
            if (!$u) return null;
            $name  = $u->name ?? 'User';
            $words = preg_split('/\s+/', trim($name));
            $initials = count($words) >= 2
                ? strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1))
                : strtoupper(mb_substr($name, 0, 2));
            return (object)[
                'user_id'      => $u->user_id,
                'name'         => $name,
                'initials'     => $initials,
                'state'        => trim(implode(', ', array_filter([$u->city, $u->state]))),
                'active_ads'   => Advert::where('user_id', $u->user_id)->activeNotRecentlySold()->count(),
                'is_following' => in_array($u->user_id, $myFollowingIds),
                'is_self'      => $loggedInUserId == $u->user_id,
                'profile_url'  => '/seller/' . \Illuminate\Support\Str::slug($name) . '/' . $u->user_id,
            ];
        })->filter()->values();

        return view('public.seller-social', [
            'title'           => $owner->name . "'s Following — " . config('global.site_name'),
            'owner'           => $owner,
            'user'            => $user,
            'isOwner'         => $isOwner,
            'loggedIn'        => $loggedIn,
            'people'          => $people,
            'paginator'       => $rows,
            'tab'             => 'following',
            'followers_count' => \App\Models\Followers::where('follow', $id)->count(),
            'following_count' => \App\Models\Followers::where('user_id', $id)->count(),
            'sellerSlug'      => $slug,
        ]);
    }

}
