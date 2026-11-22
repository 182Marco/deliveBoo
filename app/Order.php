<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['price', 'customer_name', 'customer_lastName', 'customer_email', 'customer_phone', 'customer_address', 'restaurant_id'])]
class Order extends Model
{
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function plates(): BelongsToMany
    {
        return $this->belongsToMany(Plate::class);
    }
}
