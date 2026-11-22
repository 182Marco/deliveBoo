<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// One of the 14 allergens of Annex II, Regulation (EU) 1169/2011
#[Fillable(['code', 'name', 'annex_number', 'description'])]
#[WithoutTimestamps]
class Allergen extends Model
{
    // statuses of a restaurant declaration (allergen_plate)
    public const CONTAINS = 'contains';
    public const MAY_CONTAIN = 'may_contain';

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class);
    }

    public function plates(): BelongsToMany
    {
        return $this->belongsToMany(Plate::class)->withPivot('status');
    }
}
