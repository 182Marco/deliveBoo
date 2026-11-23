<?php

use App\Http\Controllers\Admin\AvatarController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PlateController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Platform\ChartController;
use App\Http\Controllers\Platform\MapController;
use App\Http\Controllers\Platform\TableController;
use App\Http\Controllers\Platform\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Loaded by bootstrap/app.php inside the "web" middleware group.
| URIs and names are pinned by tests/Feature/RoutesTest.php.
*/

Auth::routes(); // login, register, password reset (laravel/ui)

// ROTTE AREA ADMIN
Route::prefix('admin')
    ->middleware(['auth', 'role:restaurateur'])
    ->name('admin.')
    ->group(function () {
        // home admin
        Route::get('/', [RestaurantController::class, 'index'])->name('home');
        // resource restaurants
        Route::resource('restaurants', RestaurantController::class);
        // plates by restaurant slug
        Route::controller(PlateController::class)->group(function () {
            Route::get('plates/{slug}', 'index')->name('plates.index');
            Route::get('plate/create/{slug}', 'create')->name('plates.create');
            Route::get('plates/edit/{slug}', 'edit')->name('plates.edit');
            Route::patch('plates/update/{id}', 'update')->name('plates.update');
            Route::post('plate/store', 'store')->name('plates.store');
            Route::get('plate/show/{slug}', 'show')->name('plates.show');
            Route::delete('plate/delete/{id}', 'destroy')->name('plate.delete');
        });
        // orders
        Route::controller(OrderController::class)->group(function () {
            Route::get('orders/{slug}', 'index')->name('orders.index');
            Route::get('orders/show/{id}', 'show')->name('orders.show');
        });
        // stats
        Route::resource('stats', StatsController::class)->only(['index', 'show']);
        // avatar of the logged-in user: editor page (packages/svg-avatar) and save
        Route::get('avatar', [AvatarController::class, 'edit'])->name('avatar.edit');
        Route::put('avatar', [AvatarController::class, 'update'])->name('avatar.update');
    });

// PLATFORM BACKOFFICE: admins only. One group, one authorization for the four tabs
// (role:admin + the "platform" Gate; "active" already runs on every web request).
Route::prefix('platform')
    ->middleware(['auth', 'role:admin', 'can:platform'])
    ->name('platform.')
    ->group(function () {
        Route::redirect('/', '/platform/maps')->name('home');
        Route::get('maps', [MapController::class, 'index'])->name('maps');
        Route::get('charts', [ChartController::class, 'index'])->name('charts');
        Route::get('tables/{section?}', [TableController::class, 'index'])->name('tables')
            ->whereIn('section', TableController::SECTIONS);
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        // any other /platform path is a 404 here, not the Vue site of the catch-all
        Route::any('{any}', fn () => abort(404))->where('any', '.*')->name('missing');
    });

// language switch of the partner hub: sets the foodsy_locale cookie and goes back
Route::get('locale/{locale}', LocaleController::class)->name('locale');

// front office: the Vue SPA handles every other path. MUST stay last.
Route::get('{any?}', function () {
    return view('guest.home');
})->where('any', '.*');
