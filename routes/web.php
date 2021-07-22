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
        // routes resource restaurants
        Route::resource('/plates', 'PlateController');
        // routes resource orders
        Route::resource('/orders', 'OrderController');
      });

// front office
Route::get('{any?}', function () {
   return view('guest.home');
})->where("any", ".*");