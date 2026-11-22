<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// A dietary characteristic of a plate (vegan, gluten free, low nickel, …).
// Four states: no row = not declared, unknown = declared as not verified, yes / no.
#[Fillable(['code', 'name', 'kind', 'description'])]
#[WithoutTimestamps]
class DietaryTag extends Model
{
    public const YES = 'yes';
    public const NO = 'no';
    public const UNKNOWN = 'unknown';
    public const STATUSES = [self::YES, self::NO, self::UNKNOWN];

    // who stated it
    public const FROM_RESTAURANT = 'restaurant';
    public const FROM_INGREDIENTS = 'ingredients';

    public function plates(): BelongsToMany
    {
        return $this->belongsToMany(Plate::class)->withPivot('status', 'source');
    }
}
