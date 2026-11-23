<?php

namespace App\Providers;

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
    }
}
