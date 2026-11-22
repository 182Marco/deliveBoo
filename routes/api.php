<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\TypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Loaded by bootstrap/app.php with the "api" prefix and middleware group.
| Shapes of the responses are pinned by tests/Feature/Api/PublicApiTest.php.
*/

// all types
Route::get('/types', [TypeController::class, 'index']);

Route::controller(RestaurantController::class)->group(function () {
    // top rated restaurants
    Route::get('/restaurantsTopRated', 'restTopRated');
    // restaurants by chosen types (comma separated ids)
    Route::get('/restaurants/{ids}', 'restByTypes');
    // restaurant menu
    Route::get('/restaurantsMenu/{slug}', 'restMunu');
});

// payment driver and client token for the card form of the Payment page
Route::get('/payments/config', [PaymentController::class, 'config'])->middleware('throttle:30,1');
// receive orders: the cart is charged server side (app/Payments) before confirming
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
