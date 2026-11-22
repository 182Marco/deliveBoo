<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// one service of a restaurant: weekday 0 = Monday … 6 = Sunday; closes_at < opens_at = after midnight
#[Fillable(['restaurant_id', 'weekday', 'opens_at', 'closes_at'])]
#[WithoutTimestamps]
class OpeningHour extends Model
{
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
