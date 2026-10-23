<?php

use App\Http\Controllers\Admin\ManageBuyDirect;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ManageAdverts;
use App\Http\Controllers\Admin\ManageBoost;
use App\Http\Controllers\Admin\ManageCategories;
use App\Http\Controllers\Admin\ManageUsers;
use App\Http\Controllers\Admin\ManageAdvertising;
use App\Http\Controllers\Admin\ManagePayments;
use App\Http\Controllers\Admin\ManageAdminUsers;
use App\Http\Controllers\Admin\ManageBlog;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\AdminBoostTypeController;
use App\Http\Controllers\Admin\AdminBoostDurationController;
use App\Http\Controllers\Admin\AdminSisterSiteController;
use App\Http\Controllers\Admin\CategoryUIAdminController;
use App\Http\Controllers\Admin\Admin2FAController;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
| All routes require an active admin session (adminsession middleware).
| Dashboard and all management routes also require 2FA verification.
*/

// 2FA routes — require login but not yet 2FA verified
Route::middleware('adminsession')->group(function () {
    Route::get('/admin/2fa/setup', [Admin2FAController::class, 'setup'])->name('admin.2fa.setup');
    Route::post('/admin/2fa/setup', [Admin2FAController::class, 'confirmSetup'])->name('admin.2fa.confirm');
    Route::get('/admin/2fa/verify', [Admin2FAController::class, 'verify'])->name('admin.2fa.verify');
    Route::post('/admin/2fa/verify', [Admin2FAController::class, 'verifyCode'])->name('admin.2fa.verify.code');
});

// All admin panel routes — require login AND 2FA
Route::middleware(['adminsession', 'admin.2fa'])->group(function () {

    // Dashboard
    Route::get('/admin/index', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

    // Finance (Finance role or super_admin)
    Route::middleware(['adminrole:Finance,super_admin'])->group(function () {
        Route::get('/admin/completed-payments', [ManagePayments::class, 'completed_payments'])->name('admin.completed.payments');
        Route::get('/admin/pending-payments', [ManagePayments::class, 'pending_payments'])->name('admin.pending.payments');
        Route::post('/admin/update-payment/{id}', [ManagePayments::class, 'update_payment'])->name('admin.update.payment');
        Route::post('/admin/confirm-payment/{id}', [ManagePayments::class, 'confirm_payment'])->name('admin.confirm.payment');
        Route::get('/admin/pending-settlements', [ManagePayments::class, 'pending_settlements'])->name('admin.pending.settlements');
        Route::get('/admin/completed-settlements', [ManagePayments::class, 'completed_settlements'])->name('admin.completed.settlements');
        Route::post('/admin/confirm-settlement/{id}', [ManagePayments::class, 'confirm_settlement'])->name('admin.confirm.settlement');
        Route::post('/admin/payout/{id}', [ManagePayments::class, 'sendPayout'])->name('payout.transfer');

        // Buy Direct escrow: refunds, disputes, admin cancel
        Route::get('/admin/buy-direct/refunds', [ManageBuyDirect::class, 'refunds'])->name('admin.buydirect.refunds');
        Route::post('/admin/buy-direct/refunds/{id}/retry', [ManageBuyDirect::class, 'refund'])->name('admin.buydirect.refund');
        Route::post('/admin/buy-direct/refunds/{id}/mark-refunded', [ManageBuyDirect::class, 'markRefunded'])->name('admin.buydirect.mark-refunded');
        Route::post('/admin/buy-direct/orders/{id}/cancel', [ManageBuyDirect::class, 'cancel'])->name('admin.buydirect.cancel');
        Route::get('/admin/buy-direct/disputes', [ManageBuyDirect::class, 'disputes'])->name('admin.buydirect.disputes');
        Route::post('/admin/buy-direct/disputes/{id}/resolve', [ManageBuyDirect::class, 'resolveDispute'])->name('admin.buydirect.resolve');
    });

    // Advert management
    Route::middleware(['admin.permission:create_advert,update_advert,delete_advert,view_adverts'])->group(function () {
        Route::match(['GET', 'POST'], '/admin/create-advert', [ManageAdvertising::class, 'create_advert'])->name('admin.create.advert');
        Route::delete('/admin/delete-advert/{id}', [ManageAdvertising::class, 'delete_advert'])->name('admin.delete.advert');
        Route::put('/admin/update-advert/{id}', [ManageAdvertising::class, 'updateAdvert'])->name('admin.update.advert');
        Route::get('/admin/active-adverts', [ManageAdverts::class, 'active_adverts'])->name('admin.active.adverts');
        Route::get('/admin/disabled-adverts', [ManageAdverts::class, 'disabled_adverts'])->name('admin.disabled.adverts');
        Route::get('/admin/sold-adverts', [ManageAdverts::class, 'sold_adverts'])->name('admin.sold.adverts');
        Route::get('/admin/pending-review-adverts', [ManageAdverts::class, 'pending_review_adverts'])->name('admin.pending.review.adverts');
        Route::get('/admin/advert-status/{id}/{status}', [ManageAdverts::class, 'advert_status'])->name('admin.advert.status');
        Route::post('/admin/advert-ban/{id}', [ManageAdverts::class, 'banAdvert'])->name('admin.advert.ban');
        Route::post('/admin/advert-approve/{id}', [ManageAdverts::class, 'approveAdvert'])->name('admin.advert.approve');
        Route::post('/admin/advert-reject/{id}', [ManageAdverts::class, 'rejectResubmission'])->name('admin.advert.reject');
        Route::get('/admin/sold-status/{id}/{status}', [ManageAdverts::class, 'sold_status'])->name('admin.sold.status');
        Route::get('/admin/redirect-status/{id}/{status}', [ManageAdverts::class, 'redirect_status'])->name('admin.redirect.status');
        Route::match(['GET', 'POST'], '/admin/edit-ad/{id}', [ManageAdverts::class, 'edit_advert'])->name('admin.edit.advert');
        Route::delete('/admin/delete-ad/{id}', [ManageAdverts::class, 'delete_advert'])->name('admin.delete.ad');
    });

    // Boost management
    Route::middleware(['admin.permission:view_active_boosts,manage_boost_status,manage_boost_payment'])->group(function () {
        Route::get('/boost/active', [ManageBoost::class, 'active'])->name('admin.boost.active');
        Route::get('/boost/completed', [ManageBoost::class, 'completed'])->name('admin.boost.completed');
        Route::get('/boost/unpaid', [ManageBoost::class, 'unpaid'])->name('admin.boost.unpaid');
        Route::get('/boost/confirm-payment', [ManageBoost::class, 'confirmPayment'])->name('admin.boost.confirm-payment');
        Route::get('/boost/paid', [ManageBoost::class, 'paid'])->name('admin.boost.paid');
        Route::post('/boost/status/{id}/{status}', [ManageBoost::class, 'status'])->name('admin.boost.status');
        Route::post('/boost/payment-status/{id}/{status}', [ManageBoost::class, 'payment'])->name('admin.boost.payment.status');
        Route::post('/boost/verify-and-activate/{id}', [ManageBoost::class, 'verifyAndActivate'])->name('admin.boost.verify-activate');
    });

    // Unmatched payments (own permission scope — write action, not covered by view-only boost permissions)
    Route::middleware(['admin.permission:manage_boost_payment'])->group(function () {
        Route::get('/boost/unmatched-payments', [ManageBoost::class, 'unmatchedPayments'])->name('admin.boost.unmatched-payments');
        Route::post('/boost/unmatched-payments/{id}/complete', [ManageBoost::class, 'completeUnmatchedPayment'])->name('admin.boost.unmatched-payments.complete');
    });

    // Boost settings
    Route::prefix('admin/boost-settings')->middleware(['admin.permission:manage_settings'])->group(function () {
        Route::get('/types', [AdminBoostTypeController::class, 'index'])->name('admin.boost-types.index');
        Route::post('/types', [AdminBoostTypeController::class, 'store'])->name('admin.boost-types.store');
        Route::put('/types/{id}', [AdminBoostTypeController::class, 'update'])->name('admin.boost-types.update');
        Route::delete('/types/{id}', [AdminBoostTypeController::class, 'destroy'])->name('admin.boost-types.destroy');
        Route::post('/types/{id}/toggle', [AdminBoostTypeController::class, 'toggleStatus'])->name('admin.boost-types.toggle');
        Route::get('/durations', [AdminBoostDurationController::class, 'index'])->name('admin.boost-durations.index');
        Route::post('/durations', [AdminBoostDurationController::class, 'store'])->name('admin.boost-durations.store');
        Route::put('/durations/{id}', [AdminBoostDurationController::class, 'update'])->name('admin.boost-durations.update');
        Route::delete('/durations/{id}', [AdminBoostDurationController::class, 'destroy'])->name('admin.boost-durations.destroy');
        Route::post('/durations/{id}/toggle', [AdminBoostDurationController::class, 'toggleStatus'])->name('admin.boost-durations.toggle');
    });

    // Sister sites (other-country marketplace links shown in the footer)
    Route::prefix('admin/sister-sites')->middleware(['admin.permission:manage_settings'])->group(function () {
        Route::get('/', [AdminSisterSiteController::class, 'index'])->name('admin.sister-sites.index');
        Route::post('/', [AdminSisterSiteController::class, 'store'])->name('admin.sister-sites.store');
        Route::put('/{id}', [AdminSisterSiteController::class, 'update'])->name('admin.sister-sites.update');
        Route::delete('/{id}', [AdminSisterSiteController::class, 'destroy'])->name('admin.sister-sites.destroy');
        Route::post('/{id}/toggle', [AdminSisterSiteController::class, 'toggleStatus'])->name('admin.sister-sites.toggle');
    });

    // Category management
    Route::middleware(['admin.permission:manage_categories'])->group(function () {
        Route::match(['GET', 'POST'], '/admin/category', [ManageCategories::class, 'category'])->name('admin.category');
        Route::delete('/admin/delete-category/{id}', [ManageCategories::class, 'delete_category'])->name('admin.delete.category');
        Route::match(['GET', 'POST'], '/admin/sub-category/{id}', [ManageCategories::class, 'sub_category'])->name('admin.sub.category');
        Route::delete('/admin/delete-subcategory/{id}/{cat}', [ManageCategories::class, 'delete_subcategory'])->name('admin.delete.subcategory');
        Route::delete('/admin/delete-subcategory-icon/{id}', [ManageCategories::class, 'delete_subcategory_icon'])->name('admin.delete.subcategory.icon');
        Route::match(['GET', 'POST'], '/admin/brand/{id}', [ManageCategories::class, 'brand'])->name('admin.brand');
        Route::delete('/admin/delete-brand/{id}/{cat}', [ManageCategories::class, 'delete_brand'])->name('admin.delete.brand');
        Route::match(['GET', 'POST'], '/admin/model/{id}', [ManageCategories::class, 'model'])->name('admin.model');
        Route::delete('/admin/delete-model/{id}/{cat}', [ManageCategories::class, 'delete_model'])->name('admin.delete.model');
    });

    // Category UI configuration
    Route::middleware(['admin.permission:manage_categories'])
        ->prefix('admin/category-ui')
        ->name('admin.category-ui.')
        ->group(function () {
            Route::get('/', [CategoryUIAdminController::class, 'index'])->name('index');
            Route::get('/category/{id}/edit', [CategoryUIAdminController::class, 'editCategory'])->name('edit-category');
            Route::post('/category/{id}', [CategoryUIAdminController::class, 'updateCategory'])->name('update-category');
            Route::delete('/category/{id}', [CategoryUIAdminController::class, 'deleteCategory'])->name('delete-category');
            Route::get('/subcategory/{id}/edit', [CategoryUIAdminController::class, 'editSubcategory'])->name('edit-subcategory');
            Route::post('/subcategory/{id}', [CategoryUIAdminController::class, 'updateSubcategory'])->name('update-subcategory');
            Route::delete('/subcategory/{id}', [CategoryUIAdminController::class, 'deleteSubcategory'])->name('delete-subcategory');
        });

    // Reports / resolution
    Route::middleware(['admin.permission:view_reports,manage_report_status'])->group(function () {
        Route::get('/admin/view-reports', [AdminController::class, 'view_reports'])->name('admin.view.reports');
        Route::get('/admin/report-status/{id}/{status}', [AdminController::class, 'report_status'])->name('admin.report.status');
        Route::delete('/admin/delete-complaint/{id}', [AdminController::class, 'deleteComplaint'])->name('admin.delete.complaint');
    });

    // User management
    Route::middleware(['admin.permission:view_users,manage_user_status,verify_users'])->group(function () {
        Route::get('/admin/active-users', [ManageUsers::class, 'active_users'])->name('admin.active.users');
        Route::any('/admin/user-status/{id}/{status}', [ManageUsers::class, 'user_status'])->name('admin.user.status');
        Route::post('/admin/disable-status/{id}', [ManageUsers::class, 'disable_status'])->name('admin.disable.status');
        Route::get('/admin/unverified-users', [ManageUsers::class, 'unverified_users'])->name('admin.unverified.users');
        Route::get('/admin/disabled-users', [ManageUsers::class, 'disabled_users'])->name('admin.disabled.users');
        Route::get('/admin/view-user/{id}', [ManageUsers::class, 'view_user'])->name('admin.view.user');
        Route::get('/admin/user-activity/{id}', [ManageUsers::class, 'user_activity'])->name('admin.user.activity');
        Route::get('/admin/user-verification', [ManageUsers::class, 'user_verification'])->name('admin.user.verification');
        Route::get('/admin/verify-status/{id}/{status}/{verify}', [ManageUsers::class, 'verify_status'])->name('admin.verify.status');
        Route::get('/admin/users/search', [ManageUsers::class, 'search'])->name('admin.users.search');
        Route::delete('/admin/delete-user/{id}', [ManageUsers::class, 'delete_user'])->name('admin.delete.user');
    });

    // Shipping management
    Route::middleware(['admin.permission:manage_shipping'])->group(function () {
        Route::get('/settings/setup-shipping', [SettingController::class, 'shipping'])->name('admin.setup.shipping');
        Route::put('/settings/update-shipping/{id}', [SettingController::class, 'shipping'])->name('admin.update.shipping');
    });

    // Super admin only
    Route::middleware(['adminrole:super_admin'])->group(function () {
        Route::prefix('settings/roles')->group(function () {
            Route::get('/', [RolePermissionController::class, 'index'])->name('admin.roles.index');
            Route::post('/roles', [RolePermissionController::class, 'storeRole'])->name('admin.roles.store');
            Route::post('/permissions', [RolePermissionController::class, 'storePermission'])->name('admin.permissions.store');
            Route::post('/roles/{role}/assign', [RolePermissionController::class, 'assignPermission'])->name('admin.roles.assign');
            Route::post('/admins/{admin}/assign', [RolePermissionController::class, 'assignRoleToAdmin'])->name('admin.admins.assign');
        });

        Route::match(['GET', 'POST'], '/settings/gig-locations', [SettingController::class, 'gig_locations'])->name('admin.gig.locations');
        Route::delete('/settings/delete-gig-location/{id}', [SettingController::class, 'delete_gig_location'])->name('admin.delete.gig.location');
        Route::post('/settings/update-gig-location', [SettingController::class, 'updateGigLocation'])->name('update.gig.location');

        Route::prefix('settings/manage-admins')->group(function () {
            Route::get('/', [ManageAdminUsers::class, 'index']);
            Route::post('/', [ManageAdminUsers::class, 'store']);
            Route::put('/{id}', [ManageAdminUsers::class, 'update']);
            Route::delete('/{id}', [ManageAdminUsers::class, 'destroy']);
            Route::post('/{id}/reset-2fa', [ManageAdminUsers::class, 'reset2fa'])->name('admin.reset.2fa');
        });

        Route::match(['GET', 'POST'], '/settings/ad-images', [SettingController::class, 'imageSettings'])->name('admin.ad.image.settings');
        Route::match(['GET', 'POST'], '/settings/buy-direct', [ManageBuyDirect::class, 'settings'])->name('admin.buydirect.settings');
    });

    // Blog management
    Route::resource('/admin/blogs', ManageBlog::class);
    Route::post('/tinymce/upload', [ManageBlog::class, 'upload'])->name('tinymce.upload');
    Route::post('/blogs/{blog}/toggle-status', [ManageBlog::class, 'toggleStatus'])->name('blogs.toggleStatus');
});
