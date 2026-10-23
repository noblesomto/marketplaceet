<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserProfile;
use App\Http\Controllers\User\UserManageAdverts;
use App\Http\Controllers\User\UserManageBoost;
use App\Http\Controllers\User\BlockUser;
use App\Http\Controllers\User\MessageController;
use App\Http\Controllers\User\FlutterwaveController;

/*
|--------------------------------------------------------------------------
| User Dashboard Routes
|--------------------------------------------------------------------------
| All routes require an active user session (usersession middleware).
*/

Route::middleware('usersession')->group(function () {

    // Dashboard overview
    Route::get('/user/index', [UserProfile::class, 'profile'])->name('user.index');
    Route::get('/user/my-ads', [UserController::class, 'my_ads'])->name('user.my.ads');
    Route::get('/user/boosted', [UserController::class, 'boosted_ads'])->name('user.boosted');
    Route::get('/user/payment', [UserController::class, 'payments'])->name('user.payments');
    Route::get('/user/resume-payment/{id}', [FlutterwaveController::class, 'resumePayment'])->name('user.resume.payment');
    Route::post('/user/confirm-delivery/{id}', [UserController::class, 'confirmDelivery'])->name('user.confirm.delivery');
    Route::post('/user/report-problem/{id}', [UserController::class, 'reportProblem'])->name('user.report.problem');
    Route::post('/user/cancel-order/{id}', [UserController::class, 'cancelOrder'])->name('user.cancel.order');
    Route::get('/user/ad-shipping/{id}', [UserController::class, 'ad_shipping'])->name('user.ad.shipping');
    Route::post('/user/update-shipping/{id}', [UserController::class, 'update_shipping'])->name('user.update.shipping');
    Route::get('/user/ad-status/{status}/{id}', [UserController::class, 'ad_status'])->name('user.ad.status');
    Route::get('/user/resubmit-ad/{id}', [UserController::class, 'resubmitAd'])->name('user.ad.resubmit');
    Route::get('/user/category', [UserController::class, 'category'])->name('user.category');
    Route::get('/user/orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/user/order-details/{id}', [UserController::class, 'order_details'])->name('user.order.details');
    Route::get('/user/messages', [UserController::class, 'messages'])->name('user.messages');
    Route::get('/user/archived-messages', [UserController::class, 'archivedMessages'])->name('user.archived.messages');
    Route::get('/user/feedbacks', [UserController::class, 'feedbacks'])->name('user.feedbacks');
    Route::post('/user/add-wishlist/{id}', [UserController::class, 'add_wishlist'])->name('user.add.wishlist');
    Route::get('/user/favourites', [UserController::class, 'favourites'])->name('user.favourites');
    Route::get('/user/mark-sold/{id}', [UserController::class, 'advert_sold'])->name('user.mark.sold');
    Route::post('/reviews/feedbacks/{id}', [UserController::class, 'submit_feedback'])->name('reviews.feedback');
    Route::get('/user/notifications', [UserController::class, 'notifications'])->name('user.notifications');
    Route::delete('/user/delete-notification/{id}', [UserController::class, 'deleteNotification'])->name('user.delete.notification');
    Route::match(['GET', 'POST'], 'report-user/{id}', [UserController::class, 'report_user'])->name('report.user');
    Route::get('/user/ads/load-more', [UserController::class, 'loadMoreUserAds'])->name('user.ads.loadMore');
    Route::get('/user/chat-buyer/{user}/{id}', [UserController::class, 'chat_buyer'])->name('user.chat.buyer');

    // Block / unblock
    Route::post('user/block', [BlockUser::class, 'block']);
    Route::delete('user/unblock', [BlockUser::class, 'unblock']);
    Route::get('user/blocked-users', [BlockUser::class, 'blockedList']);

    // Messages
    Route::post('/messages', [MessageController::class, 'sendMessage']);
    Route::get('/my-messages/{id}', [MessageController::class, 'fetchMyMessages']);
    Route::get('/chat/{advertId}/{receiverId}', [MessageController::class, 'showMessages'])->name('chat.show');
    Route::post('/chat/{advertId}/{receiverId}', [MessageController::class, 'sendMessage'])->name('chat.sendMessage');
    Route::get('/payment/mark-received/{id}', [MessageController::class, 'mark_received']);
    Route::post('/messages/archive', [MessageController::class, 'archive'])->name('messages.archive');
    Route::delete('/messages/unarchive', [MessageController::class, 'unarchive'])->name('messages.unarchive');
    Route::get('/messages/archived', [MessageController::class, 'archivedList'])->name('messages.archived');

    // Manage ads
    Route::match(['GET', 'POST'], '/user/post-ad', [UserManageAdverts::class, 'post_ad'])
        ->name('post.ad')
        ->middleware('profile.complete:phone');
    Route::get('/user/edit-ad/{id}', [UserManageAdverts::class, 'edit_ad'])->name('edit.ad');
    Route::post('/user/edit-ad/{id}', [UserManageAdverts::class, 'edit_ad'])->name('update.ad');
    Route::delete('/user/delete-ad/{id}', [UserManageAdverts::class, 'delete_ad'])->name('delete.ad');

    // Manage boosts
    Route::match(['GET', 'POST'], '/user/post-boost-ad/{id}', [UserManageBoost::class, 'post_boost_ad'])->name('post.boost.ad');
    Route::match(['GET', 'POST'], '/user/make-payment/{id}', [UserManageBoost::class, 'make_payment'])->name('make.payment');
    Route::match(['GET', 'POST'], '/user/boost-ad/{id}', [UserManageBoost::class, 'boost_ad'])->name('boost.ad');
    Route::get('/user/boosted-ad/{id}', [UserManageBoost::class, 'boosted_ad'])->name('boosted.ad');
    Route::post('/boost/upload-proof', [UserManageBoost::class, 'upload_proof'])->name('boost.upload.proof');

    // Profile & settings
    Route::get('/user/about-account', [UserProfile::class, 'about_account'])->name('user.about.account');
    Route::match(['GET', 'POST'], '/user/profile', [UserProfile::class, 'profile'])->name('user.profile');
    Route::match(['GET', 'POST'], '/user/profile-update', [UserProfile::class, 'profileUpdate'])->name('user.profile.update');
    Route::get('/user/settings', [UserProfile::class, 'settings'])->name('user.settings');
    Route::match(['GET', 'POST'], '/user/profile-address', [UserProfile::class, 'profile_address'])->name('user.profile.address');
    Route::match(['GET', 'POST'], '/user/profile-info', [UserProfile::class, 'profile_info'])->name('user.profile.info');
    Route::get('/user/get-verified', [UserProfile::class, 'get_verified'])->name('user.get.verified');
    Route::post('/user/submit-verification', [UserProfile::class, 'submit_verification'])->name('user.submit.verification');
    Route::match(['GET', 'POST'], '/user/payments', [UserProfile::class, 'payment_info'])->name('user.payment.info');
    Route::match(['GET', 'POST'], '/user/profile-phone', [UserProfile::class, 'profile_phone'])->name('user.profile.phone');
    Route::match(['GET', 'POST'], '/user/change-password', [UserProfile::class, 'change_password'])->name('user.change.password');
    Route::match(['GET', 'POST'], '/user/disable-account', [UserProfile::class, 'disable_account'])->name('user.disable.account');
    Route::get('/user/logout', [UserProfile::class, 'logout'])->name('user.logout');
    Route::match(['GET', 'POST'], '/user/profile-notification', [UserProfile::class, 'profile_notification'])->name('user.profile.notification');
    Route::post('/update-notifications', [UserProfile::class, 'updateNotifications'])->name('user.update-notifications');
    Route::get('/user/myads/load-more', [UserProfile::class, 'loadMoreUserAds'])->name('user.myads.loadMore');

    // Flutterwave – boosts
    Route::post('post-boost/pay', [FlutterwaveController::class, 'initialize_post_boost'])->name('post-boost.pay');
    Route::post('boost/pay', [FlutterwaveController::class, 'initialize_boost'])->name('boost.pay');
    Route::post('boost/make-payment', [FlutterwaveController::class, 'retry_boost_payment'])->name('boost.retry-payment');
    Route::get('/boost/callback', [FlutterwaveController::class, 'callback_boost'])->name('boost.callback');

    // Broadcasting auth
    Route::post('/broadcasting/auth', function (Illuminate\Http\Request $request) {
        return Broadcast::auth($request);
    });
});
