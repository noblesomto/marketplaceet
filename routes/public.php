<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pages\RobotsController;
use App\Http\Controllers\Pages\PageController;
use App\Http\Controllers\User\AdvertController;
use App\Http\Controllers\Shop\SearchFilter;
use App\Http\Controllers\Shop\LocationController;
use App\Http\Controllers\User\MessageController;
use App\Http\Controllers\User\FlutterwaveController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Admin\ManageCategories;

/*
|--------------------------------------------------------------------------
| Public Routes (no authentication required)
|--------------------------------------------------------------------------
| Static pages, advert browsing, search/filter, payment callbacks, and
| other publicly accessible endpoints.
| NOTE: Wildcard catch-all routes are registered last in web.php.
*/

// Static pages
Route::get('/', [AdvertController::class, 'index'])->name('home');
Route::get('/page', [PageController::class, 'page'])->name('page');
Route::get('/about-us', [PageController::class, 'about'])->name('about');
Route::get('/career', [PageController::class, 'career'])->name('career');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy.policy');
Route::redirect('/privacy', '/privacy-policy', 301);
Route::get('/cookie-policy', [PageController::class, 'cookie'])->name('cookie.policy');
Route::get('/billing-policy', [PageController::class, 'billing'])->name('billing.policy');
Route::get('/copyright-policy', [PageController::class, 'copyright'])->name('copyright.policy');
Route::get('/dmca-policy', [PageController::class, 'dmca'])->name('dmca.policy');
Route::get('/safety-tips', [PageController::class, 'safety'])->name('safety.tips');
Route::get('/sell-online', [PageController::class, 'sell_online'])->name('sell.online');
Route::get('/our-terms', [PageController::class, 'terms'])->name('terms');
Route::get('/payments-refunds', [PageController::class, 'payments_refunds'])->name('payments.refunds');
Route::get('/how-it-works', [PageController::class, 'how_it_works'])->name('how.it.works');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [PageController::class, 'blog_details'])->name('blog.details');
Route::get('/advertise-with-us', [PageController::class, 'advertise'])->name('advertise');
Route::match(['GET', 'POST'], '/contact-us', [PageController::class, 'contact'])->name('contact');
Route::get('/shipping', [PageController::class, 'shipping'])->name('shipping');
Route::get('/email', [PageController::class, 'email'])->name('email');
Route::get('/robots.txt', [RobotsController::class, 'index']);

// Advert browsing
Route::get('/listings', [AdvertController::class, 'adverts'])->name('listings');
Route::get('/load-more-ads-desktop', [AdvertController::class, 'loadMoreAds'])->name('load.more.ads.desktop');
Route::get('/load-more-ads-mobile', [AdvertController::class, 'loadMoreAdsMobile'])->name('load.more.ads.mobile');
Route::get('/all-categories', [AdvertController::class, 'all_categories'])->name('all.categories');
Route::get('/category/{category_slug}', [AdvertController::class, 'mainCategory'])->name('category');
Route::get('/category/all-{slug}', [AdvertController::class, 'all_category'])->name('category.all');
Route::get('/category/{category_slug}/{subcat_slug}', [AdvertController::class, 'sub_category'])->name('subcategory');
Route::get('/category/{category_slug}/{subcat_slug}/all-{slug}', [AdvertController::class, 'all_subcat'])->name('subcategory.all');
Route::get('/category/{category_slug}/{subcat_slug}/{brand_slug}', [AdvertController::class, 'brand'])->name('brand');
Route::get('/category/{category_slug}/{subcat_slug}/{brand_slug}/{model_slug}', [AdvertController::class, 'brandModel'])->name('brand.model');
Route::get('/related/{ad_id}', [AdvertController::class, 'related'])->name('related.ads');
Route::get('/related/{ad_id}/load-more', [AdvertController::class, 'relatedLoadMore'])->name('related.ads.loadMore');
Route::get('/buy-direct/success', [FlutterwaveController::class, 'buyDirectSuccess'])->name('buy.direct.success');
Route::get('/buy-direct/{id}', [AdvertController::class, 'buy_direct'])->where('id', '[0-9]+')->name('buy.direct')->middleware('profile.complete:phone');
Route::post('/calculate-shipping/{id}', [AdvertController::class, 'calculate_shipping'])->where('id', '[0-9]+')->name('calculate.shipping')->middleware('profile.complete:phone');
Route::get('/buy-direct-payment/{id}', [AdvertController::class, 'buy_direct_payment'])->where('id', '[0-9]+')->name('buy.direct.payment')->middleware('profile.complete:phone');
Route::get('/seller/{id}', function () { return redirect('/'); })->name('seller.redirect');
Route::get('/seller/{name}/{id}', [AdvertController::class, 'seller'])->name('seller');
Route::get('/seller/{name}/{id}/load-more', [AdvertController::class, 'loadMoreSellerAds'])->name('seller.ads.loadMore');
Route::match(['GET', 'POST'], '/report-ad/{id}', [AdvertController::class, 'report_advert'])->middleware('usersession')->name('report.ad');
Route::match(['GET', 'POST'], '/apply/{id}', [AdvertController::class, 'apply_job'])->middleware('usersession')->name('apply.job');
Route::get('/m-category/{id}/{slug}', [AdvertController::class, 'mobile_category'])->name('mobile.category');

// Search & filter
Route::match(['GET', 'POST'], '/search', [SearchFilter::class, 'search'])->name('search');
Route::match(['GET', 'POST'], '/filter/adverts', [SearchFilter::class, 'filter'])->name('filter.adverts');
Route::match(['GET', 'POST'], '/filter/sellers', [SearchFilter::class, 'filterBySeller'])->name('filter.sellers');
Route::match(['GET', 'POST'], '/filter/buydirect', [SearchFilter::class, 'filterByBuydirect'])->name('filter.buydirect');
Route::post('/filter/car-details', [SearchFilter::class, 'filterByCarDetails'])->name('filter.car.details');
Route::post('/filter/phone-details', [SearchFilter::class, 'filterByPhoneDetails'])->name('filter.phone.details');
Route::post('/filter/computer-details', [SearchFilter::class, 'filterByComputerDetails'])->name('filter.computer.details');

// Location & shipping cost lookup
Route::get('/states', [LocationController::class, 'index']);
Route::get('/get-gig/{state_id}', [LocationController::class, 'getGIG']);
Route::post('/shipping-cost', [LocationController::class, 'getAgilityShippingCost']);

// Flutterwave – Buy Direct
Route::post('/pay', [FlutterwaveController::class, 'initialize'])->name('flutterwave.pay')->middleware(['usersession', 'profile.complete:phone']);
Route::get('/payment/callback', [FlutterwaveController::class, 'callback'])->name('flutterwave.callback')->middleware('usersession');
Route::get('/payment-success', [FlutterwaveController::class, 'success'])->name('payment.success');
Route::get('/payment-failed', [FlutterwaveController::class, 'failed'])->name('payment.failed');

// Public chat / reviews / follow
Route::get('/chat-buyer/{user_id}/{id}', [MessageController::class, 'chat_buyer'])->name('chat.buyer');
Route::get('/chat-seller/{user}/{id}', [UserController::class, 'chat_seller'])->name('chat.seller');
Route::get('/reviews/seller/{id}', [UserController::class, 'reviews_seller'])->name('reviews.seller');
Route::get('/unread-messages-count', [MessageController::class, 'countUnreadMessages'])->name('unread.messages.count');
Route::get('/api/check-following/{userId}', [UserController::class, 'checkFollowing']);
Route::post('/api/toggle-follow', [UserController::class, 'toggleFollow'])->middleware('usersession');
Route::get('/api/social/followers', [UserController::class, 'followersListJson']);
Route::get('/api/social/following', [UserController::class, 'followingListJson']);
Route::post('/api/social/remove-follower/{userId}', [UserController::class, 'removeFollower'])->middleware('usersession');
Route::get('/seller/{slug}/{id}/followers', [AdvertController::class, 'sellerFollowers']);
Route::get('/seller/{slug}/{id}/following', [AdvertController::class, 'sellerFollowing']);

// Load more (public)
Route::get('/load-more-ads', [AdvertController::class, 'loadMoreAdverts'])->name('adverts.loadMore');
Route::get('/load-ads-location', [AdvertController::class, 'loadMoreLocation'])->name('location.loadMore');
Route::get('/adverts/load-more', [SearchFilter::class, 'loadMore'])->name('search.loadMore');

// Category data fetchers (used by post-ad / edit-ad forms)
Route::get('/fetch-subcat/{cat_id}', [ManageCategories::class, 'fetch_subcat']);
Route::get('/fetch-brand/{cat_id}', [ManageCategories::class, 'fetch_brand']);
Route::get('/fetch-model/{cat_id}', [ManageCategories::class, 'fetch_model']);
