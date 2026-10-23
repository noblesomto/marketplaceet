<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AdvertController;
use App\Http\Controllers\Shop\SearchFilter;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Routes are split into focused files and loaded here in order.
| Wildcard catch-all routes are registered last so they never swallow
| more specific routes defined in the files above.
*/

require __DIR__.'/auth.php';
require __DIR__.'/public.php';
require __DIR__.'/user.php';
require __DIR__.'/admin.php';
// Courier (shipper) portal — off while sellers arrange delivery themselves.
if (config('global.shipping_carriers_enabled')) {
    require __DIR__.'/shipper.php';
}

/*
|--------------------------------------------------------------------------
| Local Development: Image Proxy
|--------------------------------------------------------------------------
| In local env, /uploads/* files don't exist locally. php artisan serve
| falls through to Laravel when a static file is missing, so we catch
| those requests here and proxy them transparently from the live server.
| This route is never registered in production.
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    Route::get('/uploads/{path}', function (string $path) {
        $response = \Illuminate\Support\Facades\Http::timeout(15)
            ->get('https://marketplace.et/uploads/' . $path);
        if ($response->successful()) {
            return response($response->body())
                ->header('Content-Type', $response->header('Content-Type') ?? 'image/webp')
                ->header('Cache-Control', 'public, max-age=86400');
        }
        abort(404);
    })->where('path', '.*');
}

/*
|--------------------------------------------------------------------------
| Wildcard Catch-All Routes (must remain last)
|--------------------------------------------------------------------------
*/

// 3-segment: /{location}/{category_slug}/{slug} → location + category + (brand | model | price-range) filter
// slug must contain at least one letter (price-range slugs like "1m-10m"
// start with a digit) so it can never collide with the purely-numeric
// advert id below, regardless of registration order. The controller tries
// brand, then model, then a fixed set of price-range slugs.
Route::get('/{location}/{category_slug}/{slug}', [SearchFilter::class, 'location_category_brand'])
    ->where('location', '[A-Za-z0-9\-\s]+')
    ->where('category_slug', '[A-Za-z0-9\-]+')
    ->where('slug', '[A-Za-z0-9\-]*[A-Za-z][A-Za-z0-9\-]*')
    ->middleware('lowercase.url')
    ->name('location.category.brand');

// 3-segment: /{location}/{slug}/{id} → advert detail page
Route::get('/{location}/{slug}/{id}', [AdvertController::class, 'advert'])
    ->where('location', '[A-Za-z0-9\-\s]+')
    ->where('slug', '[A-Za-z0-9\-]+')
    ->where('id', '[0-9]+')
    ->middleware('lowercase.url')
    ->name('advert');

// 2-segment: /{location}/{slug} → location + category filter
Route::get('/{location}/{slug}', [SearchFilter::class, 'location_router'])
    ->where('location', '[A-Za-z0-9\-\s]+')
    ->where('slug', '[A-Za-z0-9\-]+')
    ->middleware('lowercase.url')
    ->name('location.router');

// 1-segment: /{location} → location page
Route::get('/{location}', [AdvertController::class, 'location'])
    ->where('location', '[A-Za-z0-9\-\s]+')
    ->middleware('lowercase.url')
    ->name('location');
