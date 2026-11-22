<?php

use App\Http\Controllers\Api\OrderController;
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

// receive orders
Route::post('/orders', [OrderController::class, 'store']);
