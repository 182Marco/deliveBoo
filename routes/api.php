
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();

Route::namespace('Api')->group(function(){
    // get all types
    Route::get('/types', 'TypeController@index');
    // get restaurants by chosen types
    Route::get('/restaurants/{ids}', 'RestaurantController@restByTypes');
    // retaurant menu
    Route::get('/restaurantsMenu/{id}', 'RestaurantController@restMunu');
    // recive orders
    Route::post('/orders', 'OrdersController@store'); 
});