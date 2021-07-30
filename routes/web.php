<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(); // from here all the routes for which you need authentication

// ROTTE AREA ADMIN
Route::prefix('admin')
      ->namespace('Admin')
      ->middleware('auth')
      ->name('admin.')
      ->group(function() {
        // route home admin
        Route::get('/', 'RestaurantController@index')->name('home');
        // routes resource restaurants
        Route::resource('/restaurants', 'RestaurantController');
        // routes index plates by rest slug
        Route::get('plates/{slug}', ['as' => 'plates.index', 'uses' => 'PlateController@index']);
        Route::get('plate/create/{slug}', ['as' => 'plates.create', 'uses' => 'PlateController@create']);
        Route::get('plates/edit/{slug}', ['as' => 'plates.edit', 'uses' => 'PlateController@edit']);

        Route::patch('plates/update/{id}', ['as' => 'plates.update', 'uses' => 'PlateController@update']);

        Route::post('plate/store', ['as' => 'plates.store', 'uses' => 'PlateController@store']);
        Route::get('plate/show/{slug}', ['as' => 'plates.show', 'uses' => 'PlateController@show']);
        Route::delete('plate/delete/{id}', ['as' => 'plate.delete', 'uses' => 'PlateController@destroy']);
        // routes orders
        Route::get('orders/{slug}', ['as' => 'orders.index', 'uses' => 'OrderController@index']);
        Route::get('orders/show/{id}', ['as' => 'orders.show', 'uses' => 'OrderController@show']);
        // stats
        Route::resource('/stats', 'StatsController');
      });

// front office
Route::get('{any?}', function () {
   return view('guest.home');
})->where("any", ".*");