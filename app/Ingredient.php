<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// One entry of the shared ingredient registry. Origin, lactose and nickel level are reference
// data, trusted only when info_status is "verified"; nickel_level is INDICATIVE (never a guarantee).
#[Fillable(['name', 'category', 'origin', 'contains_lactose', 'nickel_level', 'info_status'])]
class Ingredient extends Model
{
    public const VERIFIED = 'verified';
    public const UNVERIFIED = 'unverified';

    // origins that are not vegetarian / not vegan
    public const ANIMAL_FLESH = ['meat', 'fish', 'shellfish', 'mollusc'];
    public const ANIMAL_PRODUCTS = ['meat', 'fish', 'shellfish', 'mollusc', 'dairy', 'egg', 'honey'];

    protected function casts(): array
    {
        return ['contains_lactose' => 'boolean'];
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('info_status', self::VERIFIED);
    }

    public function plates(): BelongsToMany
    {
        return $this->belongsToMany(Plate::class, 'plate_ingredient')->withPivot('is_main', 'position');
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class);
    }

    // the registry key used by the admin form and the seeders: "  Buffalo mozzarella " -> "buffalo mozzarella"
    public static function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }
}
