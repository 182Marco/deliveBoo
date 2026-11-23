<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\Auth\GoogleController;
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

// "Continue with Google" (404 while GOOGLE_CLIENT_ID is empty); the callback serves login and linking
Route::middleware('throttle:20,1')->group(function () {
    Route::get('auth/google', [GoogleController::class, 'redirect'])->middleware('guest')->name('google.redirect');
    Route::get('auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

// "Your account", every role: Google link, VAT number of a restaurateur created through Google
Route::middleware('auth')->group(function () {
    Route::get('account', [AccountController::class, 'show'])->name('account');
    Route::post('account/profile', [AccountController::class, 'completeProfile'])->name('account.profile');
    Route::get('account/google/link', [GoogleController::class, 'link'])->middleware('throttle:20,1')->name('account.google.link');
    Route::delete('account/google', [AccountController::class, 'unlinkGoogle'])->name('account.google.unlink');
    // avatar of the logged-in user, every role: editor page (packages/svg-avatar) and save
    Route::get('account/avatar', [AvatarController::class, 'edit'])->name('account.avatar.edit');
    Route::put('account/avatar', [AvatarController::class, 'update'])->name('account.avatar.update');
});

// ROTTE AREA ADMIN
Route::prefix('admin')
    ->middleware(['auth', 'role:restaurateur', 'profile.complete'])
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
        // the avatar editor moved to /account/avatar (every role): old links keep working
        Route::permanentRedirect('avatar', '/account/avatar');
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
        // aggregated data of the tabs as JSON (same group, same authorization)
        Route::get('data/charts', [ChartController::class, 'data'])->name('data.charts');
        Route::get('data/map', [MapController::class, 'data'])->name('data.map');
        // detail pages of the Tables tab
        Route::get('tables/orders/{order}', [TableController::class, 'order'])->whereNumber('order')->name('tables.order');
        Route::get('tables/restaurants/{restaurant}', [TableController::class, 'restaurant'])->whereNumber('restaurant')->name('tables.restaurant');
        Route::get('tables/customer', [TableController::class, 'customer'])->name('tables.customer');
        Route::get('tables/{section?}', [TableController::class, 'index'])->name('tables')
            ->whereIn('section', TableController::SECTIONS);
        // tab Users: every action also goes through UserPolicy and the audit log
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('users/{user}', [UserController::class, 'show'])->whereNumber('user')->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->whereNumber('user')->name('users.edit');
        Route::middleware('throttle:30,1')->group(function () {
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::put('users/{user}', [UserController::class, 'update'])->whereNumber('user')->name('users.update');
            Route::post('users/{user}/ban', [UserController::class, 'ban'])->whereNumber('user')->name('users.ban');
            Route::post('users/{user}/unban', [UserController::class, 'unban'])->whereNumber('user')->name('users.unban');
        });
        // any other /platform path is a 404 here, not the Vue site of the catch-all
        Route::any('{any}', fn () => abort(404))->where('any', '.*')->name('missing');
    });

// language switch of the partner hub: sets the foodsy_locale cookie and goes back
Route::get('locale/{locale}', LocaleController::class)->name('locale');

// front office: the Vue SPA handles every other path. MUST stay last.
Route::get('{any?}', function () {
    return view('guest.home');
})->where('any', '.*');
