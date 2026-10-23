<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AccountController;
use App\Http\Controllers\Admin\AdminAccount;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| Login, registration, OTP, password reset, and social auth for all
| panels (user, admin, shipper).
*/

// Social OAuth
Route::get('auth/{provider}', [AccountController::class, 'redirectToProvider']);
Route::get('auth/{provider}/callback', [AccountController::class, 'handleProviderCallback']);

// User auth
Route::match(['GET', 'POST'], '/login', [AccountController::class, 'login'])
    ->name('login')
    ->middleware('throttle:5,1');

Route::match(['GET', 'POST'], '/register', [AccountController::class, 'register'])->name('register');
Route::get('/verifyaccount/{id}/{token}', [AccountController::class, 'verifyaccount'])->name('verify.account');
Route::get('/resend-email', [AccountController::class, 'resend_email'])->name('activation.resend');

Route::match(['GET', 'POST'], '/authenticate', [AccountController::class, 'authenticate'])
    ->name('authenticate')
    ->middleware('throttle:10,1');

Route::post('/resend-otp', [AccountController::class, 'resend_otp'])->name('resend.otp');
Route::match(['GET', 'POST'], '/forgot-password', [AccountController::class, 'forgot_password'])->name('forgot.password');
Route::match(['GET', 'POST'], '/reset-password/{id}/{token}', [AccountController::class, 'reset_password'])->name('reset.password');

// Shipper login
if (config('global.shipping_carriers_enabled')) {
    Route::match(['GET', 'POST'], '/shipper', [AccountController::class, 'shipper'])->name('shipper.login');
}

// Admin auth
Route::match(['GET', 'POST'], '/admin', [AdminAccount::class, 'adminlogin'])->name('admin.login');
Route::match(['GET', 'POST'], '/admin/forgot-password', [AdminAccount::class, 'forgotPassword'])->name('admin.forgot.password');
Route::match(['GET', 'POST'], '/admin/reset-password/{admin_id}/{token}', [AdminAccount::class, 'resetPassword'])->name('admin.reset.password');
