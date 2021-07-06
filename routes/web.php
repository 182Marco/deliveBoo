<?php

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

Route::get('/', function () {
    return view('guest.welcome');
});

Auth::routes();

Route::get('/admin', 'HomeController@index')->name('home');

// authentication routes
Auth::routes();

// Route::get('/admin', 'HomeController@index')->name('admin');

// ROTTE AREA ADMIN
Route::prefix('admin')
      ->name('admin.')
      ->namespace('Admin')
      ->middleware('auth')
      ->group(function() {
        // route home admin
        Route::get('/', 'HomeController@index')->name('home');
      });

// front office
// Route::get('{any?}', function () {
//    return view('guest.home');
// })->where("any", ".*");