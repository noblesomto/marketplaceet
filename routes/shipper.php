<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\ShipperController;

/*
|--------------------------------------------------------------------------
| Shipper Routes
|--------------------------------------------------------------------------
| Routes for the separate shipper portal (shipsession middleware).
*/

Route::middleware('shipsession')->group(function () {
    Route::get('/shipper/index', [ShipperController::class, 'index'])->name('shipper.index');
    Route::post('/shipper/get-shipping', [ShipperController::class, 'get_shipping'])->name('shipper.get.shipping');
    Route::get('/shipper/order-details/{id}', [ShipperController::class, 'order_details'])->name('shipper.order.details');
    Route::post('/shipper/update-shipping/{id}', [ShipperController::class, 'update_shipping'])->name('shipper.update.shipping');
    Route::get('/shipper/logout', [ShipperController::class, 'logout'])->name('shipper.logout');
});
