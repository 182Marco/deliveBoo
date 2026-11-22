<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['price', 'customer_name', 'customer_lastName', 'customer_email', 'customer_phone', 'customer_address', 'restaurant_id'])]
// status, payment_driver, transaction_id and payment_error are not fillable on purpose:
// only the payment flow (Api\OrderController) writes them
class Order extends Model
{
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const FAILED = 'failed';

    // only the orders actually paid count for the restaurateur (list and stats)
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::PAID);
    }

    // who processed the payment, as shown to the restaurateur: seeded demo orders have no driver
    public function paymentProvider(): string
    {
        return match ($this->payment_driver) {
            'braintree' => config('services.braintree.environment') === 'production' ? 'Braintree' : 'Braintree sandbox',
            'fake' => 'Demo payment (offline)',
            default => 'Demo data',
        };
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function plates(): BelongsToMany
    {
        return $this->belongsToMany(Plate::class);
    }
}
