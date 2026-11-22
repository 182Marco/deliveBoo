<?php

namespace App\Providers;

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
    }

    public function boot(): void
    {
        // the admin views are Bootstrap 4: since Laravel 8 ->links() defaults to Tailwind
        Paginator::useBootstrapFour();
    }
}
