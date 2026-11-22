<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rate', 'restaurant_id'])]
class Rate extends Model
{
    public function rest(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
