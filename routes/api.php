<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AdvertController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\FlutterwaveController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserManageAdverts;
use App\Http\Controllers\Api\UserProfile;
use App\Http\Controllers\Api\BlockUserController;
use App\Http\Controllers\Api\UserManageBoostController;
use App\Http\Controllers\Api\UserStatsController;
use App\Http\Controllers\Api\AdvertStatsController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\NotificationSettingsController;
use App\Http\Controllers\Api\BoostController;
use App\Http\Controllers\Api\CategoryUIController;
use App\Http\Controllers\Api\AdvertisingController;
use App\Http\Controllers\Api\FlutterwaveWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


Route::middleware(['auth:sanctum', 'user.not_disabled'])->group(function () {

    // Device Token Management
    Route::prefix('device-tokens')->group(function () {
        Route::get('/', [DeviceTokenController::class, 'index']);
        Route::post('/', [DeviceTokenController::class, 'store']);
        Route::delete('/{id}', [DeviceTokenController::class, 'destroy']);
    });

    // Notification Settings
    Route::prefix('notification-settings')->group(function () {
        Route::get('/', [NotificationSettingsController::class, 'show']);
        Route::put('/', [NotificationSettingsController::class, 'update']);
    });

    // Test Push Notifications (Local/Development only)
    Route::prefix('test')->group(function () {
        Route::post('/notification', [\App\Http\Controllers\Api\TestNotificationController::class, 'sendTest']);
        Route::get('/my-tokens', [\App\Http\Controllers\Api\TestNotificationController::class, 'getMyTokens']);
    });

});
/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
// Strict throttle on credential and OTP endpoints (10 attempts per minute per IP)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AccountController::class, 'register']);
    Route::post('/login', [AccountController::class, 'login']);
    Route::post('/verify-otp', [AccountController::class, 'verifyOTP']);
    Route::post('/resend-otp', [AccountController::class, 'resendOTP']);
    Route::post('/forgot-password', [AccountController::class, 'forgotPassword']);
    Route::post('/reset-password/{user_id}/{token}', [AccountController::class, 'resetPassword']);
    Route::post('/resend-verification', [AccountController::class, 'resendVerification']);
    Route::post('/auth/social', [AccountController::class, 'socialLogin']);
    Route::post('/auth/apple', [AccountController::class, 'appleLogin']);
});

Route::get('/verify/{email}/{token}', [AccountController::class, 'verifyAccount']);
Route::get('/auth/{provider}/redirect', [AccountController::class, 'socialRedirect']);
Route::get('/auth/{provider}/callback', [AccountController::class, 'socialCallback']);

// Protected Authentication Routes
Route::middleware(['auth:sanctum', 'user.not_disabled'])->group(function () {
    Route::get('/user', [AccountController::class, 'user']);
    Route::post('/logout', [AccountController::class, 'logout']);
    Route::delete('/trusted-device/{device_id}', [AccountController::class, 'removeTrustedDevice']);
});

/*
|--------------------------------------------------------------------------
| Advert Routes (Public)
|--------------------------------------------------------------------------
*/
Route::get('/adverts', [AdvertController::class, 'index']);
// Static paths must come before wildcard {id} routes
Route::get('/adverts/featured', [AdvertController::class, 'featuredAdverts']);
Route::get('/adverts/load-more', [AdvertController::class, 'loadMore']);
Route::get('/adverts/seller/{seller_id}', [AdvertController::class, 'sellerAdverts']);
Route::get('/adverts/image-requirements', [UserManageAdverts::class, 'imageRequirements']);
Route::get('/adverts/{id}/related', [AdvertController::class, 'related']);
Route::get('/adverts/{id}', [AdvertController::class, 'show']);
Route::get('/categories', [AdvertController::class, 'categories']);
Route::get('/categories/{category_slug}', [AdvertController::class, 'categoryAdverts']);
Route::get('/categories/{category_slug}/subcategories', [AdvertController::class, 'categorySubcategories']);
Route::get('/categories/{category_slug}/{subcat_slug}', [AdvertController::class, 'subcategoryAdverts']);
Route::get('/brands/{category_slug}/{subcat_slug}/{brand_slug}', [AdvertController::class, 'brandAdverts']);
Route::get('/brands/{category_slug}/{subcat_slug}/{brand_slug}/{model_slug}', [AdvertController::class, 'brandModelAdverts']);
Route::get('/location/{state_slug}', [AdvertController::class, 'locationAdverts']);

/*
|--------------------------------------------------------------------------
| Sister Sites (Public)
|--------------------------------------------------------------------------
*/
Route::get('/sister-sites', [\App\Http\Controllers\Api\SisterSiteController::class, 'index']);

// Protected Advert Routes
Route::middleware(['auth:sanctum', 'user.not_disabled'])->group(function () {
    Route::post('/adverts/{id}/report', [AdvertController::class, 'reportAdvert']);
    Route::post('/adverts/{id}/apply', [AdvertController::class, 'applyJob']);
    Route::get('/adverts/{id}/buy-direct', [AdvertController::class, 'buy_direct']);
    Route::post('/adverts/{id}/buy-direct-payment', [AdvertController::class, 'buy_direct_payment']);
    Route::post('/shipping/calculate/{id}', [AdvertController::class, 'calculate_shipping']);
});

/*
|--------------------------------------------------------------------------
| Search Routes (Public & Enhanced)
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/search', [SearchController::class, 'search']);
    Route::post('/search/filter', [SearchController::class, 'filter']);
    Route::post('/search/filter-by-seller', [SearchController::class, 'filterBySeller']);
    Route::post('/search/filter-by-buydirect', [SearchController::class, 'filterByBuydirect']);
    Route::post('/search/filter-by-car', [SearchController::class, 'filterByCarDetails']);
    Route::post('/search/filter-by-phone', [SearchController::class, 'filterByPhoneDetails']);
    Route::get('/search/location/{location}/{slug}', [SearchController::class, 'locationSearch']);
    Route::get('/search/filters', [SearchController::class, 'getFilters']);
    Route::get('/search/suggestions', [SearchController::class, 'getSuggestions']);
    Route::get('/search/load-more', [SearchController::class, 'loadMore']);
});

/*
|--------------------------------------------------------------------------
| Advertising Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('advertising')->group(function () {
    Route::get('/', [AdvertisingController::class, 'index']);       // GET /api/advertising?type=banner
    Route::get('/{id}/click', [AdvertisingController::class, 'click']); // GET /api/advertising/{id}/click
});

/*
|--------------------------------------------------------------------------
| Location & Shipping Routes (Public)
|--------------------------------------------------------------------------
*/
Route::get('/locations/states', [LocationController::class, 'getStates']);
Route::get('/locations/states/{state}/lgas', [LocationController::class, 'getLGAsByState']);
Route::get('/locations/states/{state_id}/cities', [LocationController::class, 'getCitiesByState']);
Route::get('/locations/states/{state_id}/details', [LocationController::class, 'getStateWithCities']);
Route::get('/locations/cities', [LocationController::class, 'searchCities']);
Route::get('/locations/cities/{city_id}', [LocationController::class, 'getCity']);


/*
|--------------------------------------------------------------------------
| Category UI Configuration Routes (Public)
|--------------------------------------------------------------------------
| Database-driven UI configuration for Post Ad and Edit Ad forms
| Heavily cached for performance (24 hours)
*/
Route::get('/ui-config/all', [CategoryUIController::class, 'getUIConfig']);
Route::get('/ui-config/category/{id}', [CategoryUIController::class, 'getCategoryConfig']);
Route::get('/ui-config/subcategory/{id}', [CategoryUIController::class, 'getSubcategoryConfig']);
Route::post('/ui-config/clear-cache', [CategoryUIController::class, 'clearCache']); // For admin use

/*
|--------------------------------------------------------------------------
| Message Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'user.not_disabled', 'throttle:60,1'])->group(function () {
    Route::post('/messages', [MessageController::class, 'sendMessage']);
    Route::get('/messages/conversation/{advertId}/{receiverId}', [MessageController::class, 'getConversation']);
    Route::get('/messages/advert/{advertId}', [MessageController::class, 'getAdvertMessages']);
    Route::get('/messages/conversations', [MessageController::class, 'getUserConversations']);
    Route::get('/messages/unread/count', [MessageController::class, 'getUnreadCount']);
    Route::put('/messages/{messageId}/read', [MessageController::class, 'markAsRead']);
    Route::put('/messages/conversation/{advertId}/{userId}/read', [MessageController::class, 'markConversationAsRead']);

    // Archive functionality
    Route::post('/messages/archive', [MessageController::class, 'archive']);
    Route::post('/messages/unarchive', [MessageController::class, 'unarchive']);
    Route::get('/messages/archived', [MessageController::class, 'getArchivedConversations']);

    // Payment status
    Route::post('/payments/{paymentId}/mark-delivered', [MessageController::class, 'markAsDelivered']);
});

/*
|--------------------------------------------------------------------------
| Payment Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'user.not_disabled'])->group(function () {
    Route::post('/payments/initialize', [FlutterwaveController::class, 'initializePayment']);
    Route::post('/payments/initialize-boost', [FlutterwaveController::class, 'initializeBoost']);
    Route::get('/payments/{paymentId}', [FlutterwaveController::class, 'getPayment']);
    Route::get('/payments/user/{userId}', [FlutterwaveController::class, 'getUserPayments']);
    Route::get('/boosts/user/{userId}', [FlutterwaveController::class, 'getUserBoosts']);
});

// Public callback route (no auth required)
Route::post('/payments/callback', [FlutterwaveController::class, 'handleCallback']);

// Flutterwave webhook — server-to-server, no auth, signature verified inside the controller
Route::post('/flutterwave/webhook', [FlutterwaveWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| User Dashboard Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'user.not_disabled'])->prefix('user')->group(function () {
    // Dashboard
    Route::get('/dashboard', [UserController::class, 'dashboard']);
    Route::get('/categories', [UserController::class, 'categories']);

    // Messages
    Route::get('/messages/conversations', [UserController::class, 'conversations']);

    // Ads management
    Route::get('/ads', [UserController::class, 'myAds']);
    Route::patch('/ads/{adId}/status', [UserController::class, 'updateAdStatus']);
    Route::patch('/ads/{adId}/resubmit', [UserController::class, 'resubmitAdvert']);
    Route::patch('/ads/{adId}/mark-sold', [UserController::class, 'markAsSold']);

    // Wishlist
    Route::get('/wishlist', [UserController::class, 'wishlist']);
    Route::post('/wishlist/{adId}', [UserController::class, 'addToWishlist']);
    Route::delete('/wishlist/{adId}', [UserController::class, 'removeFromWishlist']);
    Route::post('/wishlist/{adId}/toggle', [UserController::class, 'toggleWishlist']);

    // Payments & Shipping
    Route::get('/payments', [UserController::class, 'payments']);
    Route::get('/payments/{paymentId}/details', [UserController::class, 'orderDetails']);
    Route::post('/payments/{paymentId}/resume', [UserController::class, 'resumePayment']);
    Route::post('/payments/{paymentId}/confirm-delivery', [UserController::class, 'confirmDelivery']);
    Route::post('/payments/{paymentId}/report-problem', [UserController::class, 'reportProblem']);
    Route::post('/payments/{paymentId}/cancel-order', [UserController::class, 'cancelOrder']);
    Route::patch('/payments/{paymentId}/shipping-status', [UserController::class, 'updateShippingStatus']);
    Route::get('/adverts/{advertId}/shipping-details', [UserController::class, 'adShippingDetails']);

    // Feedbacks
    Route::get('/feedbacks', [UserController::class, 'feedbacks']);
    Route::post('/feedbacks/{sellerId}', [UserController::class, 'submitFeedback']);

    // Following
    Route::get('/following/check/{userId}', [UserController::class, 'checkFollowing']);
    Route::post('/following/toggle', [UserController::class, 'toggleFollow']);
    Route::delete('/following/remove/{userId}', [UserController::class, 'removeFollower']);
});

/*
|--------------------------------------------------------------------------
| User Manage Adverts Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'user.not_disabled', 'throttle:20,1'])->prefix('adverts')->group(function () {
    // Get data for creating adverts
    Route::get('/create/data', [UserManageAdverts::class, 'getCreateData']);

    // Pre-upload images (two-step flow) — returns tokens to pass in temp_image_paths[]
    Route::post('/upload-images', [UserManageAdverts::class, 'uploadImages']);

    // Get advert for editing
    Route::get('/{advertId}/edit', [UserManageAdverts::class, 'getAdvertForEdit']);

    // CRUD routes
    Route::post('/', [UserManageAdverts::class, 'createAdvert'])->middleware('user.phone_complete');
    Route::put('/{advertId}', [UserManageAdverts::class, 'updateAdvert']);
    Route::delete('/{advertId}', [UserManageAdverts::class, 'deleteAdvert']);
});

// Public routes for category/brand/model data
Route::get('/adverts/categories/{categoryId}/subcategories', [UserManageAdverts::class, 'fetchSubcategories']);
Route::get('/adverts/subcategories/{subcategoryId}/brands', [UserManageAdverts::class, 'fetchBrands']);
Route::get('/adverts/brands/{brandId}/models', [UserManageAdverts::class, 'fetchModels']);
Route::get('/shipping-companies', [UserManageAdverts::class, 'getShippingCompanies']);

/*
|--------------------------------------------------------------------------
| User Profile Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    // Profile endpoints
    Route::get('/profile', [UserProfile::class, 'getProfile']);
    Route::get('/about-account', [UserProfile::class, 'aboutAccount']);
    Route::get('/profile-info', [UserProfile::class, 'getProfileInfo']);

    // User ads - NOTE: Removed duplicate, using UserController@myAds instead (line 172)
    // Route::get('/ads', [UserProfile::class, 'loadMoreUserAds']);

    // Profile updates
    Route::put('/profile/address', [UserProfile::class, 'updateAddress']);
    Route::post('/profile/address', [UserProfile::class, 'updateAddress']); // For form-data
    Route::put('/profile/phone', [UserProfile::class, 'updatePhone']);

    // Verification
    Route::get('/verification', [UserProfile::class, 'getVerificationStatus']);
    Route::post('/verification', [UserProfile::class, 'submitVerification']);

    // Payment information
    Route::get('/payment-info', [UserProfile::class, 'getPaymentInfo']);
    Route::put('/payment-info', [UserProfile::class, 'updatePaymentInfo']);

    // Password change
    Route::put('/password', [UserProfile::class, 'changePassword']);

    // Notification preferences
    Route::put('/notifications', [UserProfile::class, 'updateNotifications']);

    // Settings
    Route::get('/settings', [UserProfile::class, 'getSettings']);

    // Account management
    Route::delete('/account', [UserProfile::class, 'disableAccount']);
    Route::post('/logout', [UserProfile::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Block User Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'user.not_disabled'])->prefix('users')->group(function () {
    Route::post('/block', [BlockUserController::class, 'block']);
    Route::post('/unblock', [BlockUserController::class, 'unblock']);
    Route::get('/blocked', [BlockUserController::class, 'getBlockedUsers']);
    Route::get('/check-blocked/{userId}', [BlockUserController::class, 'checkBlocked']);
    Route::post('/block-all/{userId}', [BlockUserController::class, 'blockGlobally']);
});

/*
|--------------------------------------------------------------------------
| Ad Boost Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'user.not_disabled'])->group(function () {
    // User boost management
    Route::get('/user/boosts', [UserManageBoostController::class, 'getUserBoosts']);
    Route::get('/boosts/active', [UserManageBoostController::class, 'getActiveBoosts']);
    Route::get('/boosts/pending', [UserManageBoostController::class, 'getPendingBoosts']);

    // Advert boost operations
    Route::get('/adverts/{advertId}/boost-info', [UserManageBoostController::class, 'getBoostInfo']);
    Route::post('/adverts/{advertId}/boost', [UserManageBoostController::class, 'createBoost']);
    Route::get('/adverts/{advertId}/boost-status', [UserManageBoostController::class, 'checkBoostStatus']);

    // Boost management
    Route::get('/boosts/{boostId}', [UserManageBoostController::class, 'getBoost']);
    Route::post('/boosts/{boostId}/upload-proof', [UserManageBoostController::class, 'uploadProof']);
    Route::delete('/boosts/{boostId}', [UserManageBoostController::class, 'cancelBoost']);
});

/*
|--------------------------------------------------------------------------
| Boost Pricing Routes
|--------------------------------------------------------------------------
*/
Route::prefix('boost')->group(function () {
    // Public boost options (can be accessed without auth for price display)
    Route::get('/options', [BoostController::class, 'getOptions']);
    Route::post('/calculate', [BoostController::class, 'calculatePrice']);
});

// Protected routes - require authentication
Route::middleware(['auth:sanctum', 'user.not_disabled'])->group(function () {

    // User Statistics
    Route::prefix('user')->group(function () {
        Route::get('/stats', [UserStatsController::class, 'getUserStats']);
        Route::get('/unread-messages', [UserStatsController::class, 'getUnreadMessagesCount']);
        Route::get('/unread-notifications', [UserStatsController::class, 'getUnreadNotificationsCount']);
        Route::get('/notifications', [UserStatsController::class, 'getNotifications']);
        Route::put('/notifications/read-all', [\App\Http\Controllers\Api\UserController::class, 'markAllNotificationsAsRead']);
        Route::put('/notifications/{id}/read', [\App\Http\Controllers\Api\UserController::class, 'markNotificationAsRead']);
        Route::delete('/delete-notification/{id}', [\App\Http\Controllers\Api\UserController::class, 'deleteNotification']);
    });

    // Public user data (still protected but can view others)
    Route::get('/user/{userId}/followers', [UserStatsController::class, 'getUserFollowers']);
    Route::get('/user/{userId}/feedback', [UserStatsController::class, 'getUserFeedback']);

    // Advert Statistics
    Route::prefix('adverts')->group(function () {
        Route::get('/count', [AdvertStatsController::class, 'getAdvertCount']);
        Route::get('/by-state', [AdvertStatsController::class, 'getAdvertsByState']);
        Route::get('/count-filtered', [AdvertStatsController::class, 'getAdvertCountByFilter']);
        Route::get('/brands', [AdvertStatsController::class, 'getBrandsWithAdvertCount']);
    });
});

/*
|--------------------------------------------------------------------------
| Additional Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::middleware('auth:sanctum')->get('/unread-messages-count', function(Request $request) {
    $user = $request->user();

    if (!$user) {
        return response()->json(['count' => 0]);
    }

    $count = App\Models\Message::where('receiver_id', $user->user_id)
                ->where('is_read', false)
                ->count();

    return response()->json(['count' => $count]);
});
