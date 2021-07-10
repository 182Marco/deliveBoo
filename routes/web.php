<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('guest.home');
// });
// PLEASE TEAM CHECK! 
// I think we don't need those lines
//  anymore because it's already written below
// Route::get('/admin', 'HomeController@index')->name('home');
// authentication routes
// Auth::routes();
// Route::get('/admin', 'HomeController@index')->name('admin');
Auth::routes(); // from here all the routes for which you need authentication

// ROTTE AREA ADMIN
Route::prefix('admin')
      ->namespace('Admin')
      ->middleware('auth')
      ->name('admin.')
      ->group(function() {
        // route home admin
        Route::get('/', 'HomeController@index')->name('home');

        // rotte resource restaurants
        Route::resource('/restaurants', 'RestaurantController');
      });

// front office
Route::get('{any?}', function () {
   return view('guest.home');
})->where("any", ".*");