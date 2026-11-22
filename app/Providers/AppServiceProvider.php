<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // the admin views are Bootstrap 4: since Laravel 8 ->links() defaults to Tailwind
        Paginator::useBootstrapFour();
    }
}
