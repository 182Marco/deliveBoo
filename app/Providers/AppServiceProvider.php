<?php

namespace App\Providers;

use App\Order;
use App\Plate;
use App\Policies\OrderPolicy;
use App\Policies\PlatePolicy;
use App\Policies\RestaurantPolicy;
use App\Policies\UserPolicy;
use App\Restaurant;
use App\User;
use Illuminate\Support\Facades\Gate;

use App\I18n\StringsLoader;
use App\Payments\BraintreeGateway;
use App\Payments\FakeGateway;
use App\Payments\PaymentGateway;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // the payment driver is a deploy choice (PAYMENT_DRIVER), the order flow never changes
        $this->app->singleton(PaymentGateway::class, fn () => match (config('services.payments.driver')) {
            'braintree' => BraintreeGateway::fromConfig(config('services.braintree')),
            'fake' => new FakeGateway(),
            default => throw new \InvalidArgumentException('Unknown PAYMENT_DRIVER: ' . config('services.payments.driver')),
        });

        // every translation (__(), validation, mail) comes from resources/js/utils/strings.json
        $this->app->extend('translation.loader', fn ($files) => new StringsLoader($files));
    }

    public function boot(): void
    {
        // pagination markup of the Foodsy design system (resources/views/pagination/foodsy.blade.php)
        Paginator::defaultView('pagination.foodsy');
        Paginator::defaultSimpleView('pagination.foodsy');

        // ownership rules of the partner hub (SEC-1..SEC-4)
        Gate::policy(Restaurant::class, RestaurantPolicy::class);
        Gate::policy(Plate::class, PlatePolicy::class);
        // reviews: only the customer of a delivered order, within the review window
        Gate::policy(Order::class, OrderPolicy::class);
        // platform backoffice (/platform): one admin role, account actions through UserPolicy
        Gate::policy(User::class, UserPolicy::class);
        Gate::define('platform', fn (User $user) => $user->isAdmin() && ! $user->isBanned());
    }
}
