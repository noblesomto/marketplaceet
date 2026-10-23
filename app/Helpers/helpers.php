<?php

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Advertising;
use App\Models\Advert;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\ArchivedMessage;
use App\Models\User;
use App\Models\Feedback;
use App\Models\Notification;
use Illuminate\Support\Facades\Session;
use App\Helpers\AdminHelper;



if (!function_exists('currentAdmin')) {
    function currentAdmin() {
        return AdminHelper::currentAdmin();
    }
}

if (!function_exists('adminCan')) {
    function adminCan($permission) {
        return AdminHelper::can($permission);
    }
}

if (!function_exists('adminHasRole')) {
    function adminHasRole($role) {
        return AdminHelper::hasRole($role);
    }
}


if (!function_exists('getCategories')) {
    function getCategories()
    {
        return Category::all();
    }
}

if (!function_exists('getAdverts')) {
    function getAdverts()
    {
        return Advertising::inRandomOrder()->where('type', 'banner')->take(1)->get();
    }
}

if (!function_exists('getSideAdverts')) {
    function getSideAdverts()
    {
        return Advertising::inRandomOrder()->where('type', 'sidebar')->take(1)->get();
    }
}

if (!function_exists('countUserFollowers')) {
    /**
     * Count the number of followers for a specific user
     * 
     * @param int $userId The ID of the user to count followers for
     * @return int The number of followers
     */
    function countUserFollowers($userId)
    {
        return \DB::table('followers')
                 ->where('follow', $userId)
                 ->count();
    }
}


if (!function_exists('getTotalUnreadMessages')) {
    function getTotalUnreadMessages()
    {
        // Try session first (web)
        $userId = Session::get('user_id');

        // If no session, try API auth
        if (!$userId && Auth::check()) {
            $user = Auth::user();
            $userId = User::where('id', $user->id)->value('user_id');
        }

        if (!$userId) {
            return 0;
        }

        $archivedIds = ArchivedMessage::where('user_id', $userId)
            ->pluck('advert_id')
            ->toArray();

        return Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->when(!empty($archivedIds), fn($q) => $q->whereNotIn('advert_id', $archivedIds))
            ->count();
    }
}

if (!function_exists('getAdvertsGroupedByState')) {
    function getAdvertsGroupedByState(array $filters = [])
    {
        $query = Advert::select('state', DB::raw('count(*) as total'))
            ->where('ad_status', 'active')
            ->where('sold', 'No')
            ->groupBy('state')
            ->orderByDesc('total');

        // ✅ Optional filters
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['sub_category'])) {
            $query->where('sub_category', $filters['sub_category']);
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }


        return $query->get();
    }
}


if (!function_exists('getAdvertCount')) {
    function getAdvertCount(array $filters = [])
    {
        $query = Advert::query()
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['sub_category'])) {
            $query->where('sub_category', $filters['sub_category']);
        }

        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        // Filter by buy_direct
        if (isset($filters['buy_direct'])) {
            if ($filters['buy_direct'] === 'Yes') {
                $query->where('buy_direct', 'Yes');
            } elseif ($filters['buy_direct'] === 'No') {
                // force no results if "No"
                $query->whereRaw('1 = 0');
            }
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->count();
    }
}


if (!function_exists('advert_count_by_filter')) {
    /**
     * Get the total count of adverts filtered by verified users, category, sub_category, and brand.
     *
     * @param string $verified ('yes' or 'no')
     * @param int|string|null $category    Category ID or slug
     * @param int|string|null $subCategory SubCategory ID or slug
     * @param int|string|null $brand       Brand ID or slug
     * @return int
     */
    function advert_count_by_filter($verified = 'yes', $category = null, $subCategory = null, $brand = null)
    {
        $query = Advert::whereHas('owner', function ($q) use ($verified) {
                $q->where('verified', $verified);
            })
            ->where('ad_status', 'active')
            ->where('sold', 'No');

        // Category filter
        if (!empty($category)) {
            $categoryId = is_numeric($category)
                ? $category
                : Category::where('category_slug', $category)->value('id');
            if ($categoryId) {
                $query->where('category', $categoryId);
            }
        }

        // SubCategory filter
        if (!empty($subCategory)) {
            $subCategoryId = is_numeric($subCategory)
                ? $subCategory
                : SubCategory::where('sub_cat_slug', $subCategory)->value('id');
            if ($subCategoryId) {
                $query->where('sub_category', $subCategoryId);
            }
        }

        // Brand filter
        if (!empty($brand)) {
            $brandId = is_numeric($brand)
                ? $brand
                : Brand::where('brand_slug', $brand)->value('id');
            if ($brandId) {
                $query->where('brand', $brandId);
            }
        }

        return $query->count();
    }
}

if (!function_exists('setViews')) {
    /**
     * Set views count to 1000 or a specified value
     *
     * @param int|null $count Optional custom view count
     * @return int
     */
    function setViews($count = null)
    {
        return $count ?? 1000;
    }
}

if (!function_exists('get_user_feedback_averages')) {
    function get_user_feedback_averages($user_id)
    {
        $feedbacks = Feedback::where('seller_id', $user_id)->get();

        if ($feedbacks->isEmpty()) {
            return [
                'rating' => 0,
                'satisfaction' => 0,
                'reliable' => 0,
                'friendly' => 0,
                'count' => 0
            ];
        }

        return [
            'rating' => round($feedbacks->avg('rating'), 1),
            'satisfaction' => round($feedbacks->avg('satisfaction'), 1),
            'reliable' => round($feedbacks->avg('reliable'), 1),
            'friendly' => round($feedbacks->avg('friendly'), 1),
            'count' => $feedbacks->count()
        ];
    }
}

if (!function_exists('rating_label_class')) {
    function rating_label_class($average)
    {
        if ($average >= 4.0) {
            return ['label' => 'Very', 'color' => 'bg-blue-100 text-blue-800']; // Very Reliable
        } elseif ($average >= 2.0) {
            return ['label' => 'Fairly', 'color' => 'bg-yellow-100 text-yellow-800']; // Fairly Friendly
        } elseif ($average > 0) {
            return ['label' => 'Barely', 'color' => 'bg-red-100 text-red-800']; // Barely Satisfied
        } else {
            return ['label' => 'Unrated', 'color' => 'bg-gray-100 text-gray-600'];
        }
    }
}


if (!function_exists('feedback_rating_labels')) {
    function feedback_rating_labels($user_id)
    {
        $averages = get_user_feedback_averages($user_id);
        return [
            'satisfaction' => rating_label_class($averages['satisfaction']),
            'friendly' => rating_label_class($averages['friendly']),
            'reliable' => rating_label_class($averages['reliable']),
        ];

    }
}

if (!function_exists('get_brands_with_advert_count')) {
    function get_brands_with_advert_count($categoryId = null, $subCategoryId = null)
    {
        $brandsQuery = Brands::query();

        if ($subCategoryId) {
            $brandsQuery->where('subcat_id', $subCategoryId);
        } elseif ($categoryId) {
            // Get subcategory IDs that belong to this category
            $subcatIds = SubCategory::where('cat_id', $categoryId)->pluck('id')->toArray();
            $brandsQuery->whereIn('subcat_id', $subcatIds);
        }

        // Count live ads per brand in ONE grouped query. adverts.brand is a
        // varchar while brands.id is a bigint, so a correlated withCount()
        // can't use the brand index and rescans every advert per brand
        // (~5s on a single category page).
        $subcatIds = $subCategoryId
            ? [$subCategoryId]
            : ($categoryId ? SubCategory::where('cat_id', $categoryId)->pluck('id')->all() : null);

        $countsQuery = \App\Models\Advert::query()
            ->where('ad_status', 'active')
            ->where('sold', 'No')
            ->whereNotNull('brand');

        if ($subcatIds !== null) {
            $countsQuery->whereIn('sub_category', array_map('strval', $subcatIds));
        }

        $counts = $countsQuery->groupBy('brand')->selectRaw('brand, COUNT(*) as total')->pluck('total', 'brand');

        if ($counts->isEmpty()) {
            return collect();
        }

        return $brandsQuery->with('subCategory.category')
            ->whereIn('id', $counts->keys()->filter(fn ($id) => ctype_digit((string) $id))->map(fn ($id) => (int) $id)->values())
            ->get()
            ->each(fn ($brand) => $brand->setAttribute('adverts_count', (int) ($counts[(string) $brand->id] ?? 0)))
            ->filter(fn ($brand) => $brand->adverts_count > 0)
            ->values();

    }
}


if (!function_exists('getUserNotifications')) {
    function getUserNotifications($limit = 10)
    {
        // Try session first (web), then fall back to API token auth
        // Auth::id() returns the model's primary key (user_id = 5-char code)
        $userCode = Session::get('user_id') ?? (Auth::check() ? Auth::id() : null);

        $user = User::where('user_id', $userCode)->first();

        if (!$user) {
            return collect();
        }

        return Notification::with('seller', 'advert')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('getUserNotificationCount')) {
    function getUserNotificationCount()
    {
        // Try session first (web), then fall back to API token auth
        $userCode = Session::get('user_id') ?? (Auth::check() ? Auth::id() : null);

        $user = User::where('user_id', $userCode)->first();

        if (!$user) {
            return 0;
        }

        $query = Notification::where('user_id', $user->id);

        // If user has visited the notifications page before, only count
        // notifications created after their last visit. Otherwise fall back
        // to the legacy is_read flag so existing unread items still show.
        if ($user->notifications_seen_at) {
            $query->where('created_at', '>', $user->notifications_seen_at);
        } else {
            $query->where('is_read', false);
        }

        return $query->count();
    }
}
