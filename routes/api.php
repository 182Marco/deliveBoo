<?php

use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\MyOrdersController;
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
    // search by restaurant name (header search box): ?q=, at most 8 suggestions
    Route::get('/restaurantsSearch', 'search')->middleware('throttle:60,1');
    // restaurants by chosen types (comma separated ids)
    Route::get('/restaurants/{ids}', 'restByTypes');
    // restaurant menu
    Route::get('/restaurantsMenu/{slug}', 'restMunu');
});

// payment driver and client token for the card form of the Payment page
Route::get('/payments/config', [PaymentController::class, 'config'])->middleware('throttle:30,1');
// receive orders: the cart is charged server side (app/Payments) before confirming.
// active: a banned account is logged out (403) instead of ordering; a logged-in customer owns the order
Route::post('/orders', [OrderController::class, 'store'])->middleware(['active', 'throttle:10,1']);

// the logged-in account of the Vue site ({ user: null } for guests; banned accounts are logged out)
Route::get('/me', [MeController::class, 'show'])->middleware(['active', 'throttle:60,1']);
// "My orders": the orders of the logged-in account (401 for guests), paginated
Route::get('/me/orders', [MyOrdersController::class, 'index'])->middleware(['auth:sanctum', 'active', 'throttle:60,1']);
