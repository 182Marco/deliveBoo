<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'ingredients', 'description', 'course', 'visible', 'availability', 'available_from_month',
    'available_to_month', 'price', 'serves', 'calories', 'allergen_info', 'img', 'restaurant_id',
])]
// The "ingredients" column is only the display label read by the site and the API:
// the data is the ingredientList() relation, kept in sync by syncIngredients().
class Plate extends Model
{
    use SoftDeletes;

    public const AVAILABLE = 'available';
    public const SOLD_OUT = 'sold_out';
    public const UNAVAILABLE = 'unavailable';
    public const AVAILABILITIES = [self::AVAILABLE, self::SOLD_OUT, self::UNAVAILABLE];

    // restaurant declaration of the allergens (null = not declared)
    public const ALLERGENS_COMPLETE = 'complete';
    public const ALLERGENS_PARTIAL = 'partial';

    public const COURSES = [
        'starter', 'salad', 'soup', 'pasta', 'risotto', 'pizza', 'burger', 'sandwich', 'main', 'side',
        'sushi', 'ramen', 'poke', 'dessert', 'bakery', 'gelato', 'tasting_menu',
        'water', 'soft_drink', 'beer', 'wine_white', 'wine_red', 'wine_rose', 'sparkling', 'champagne',
        'dessert_wine', 'spirit', 'cocktail', 'hot_drink',
    ];

    protected function casts(): array
    {
        return [
            // DECIMAL on MySQL is read as a string: the JSON and the cart need a number
            'price' => 'float',
            'visible' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // "visible" (read by the site) always follows availability, in both directions.
        // get/setAttribute on purpose: inside the class "$plate->visible" would be the
        // protected Model::$visible property (serialization), not the column
        static::saving(function (Plate $plate) {
            if ($plate->isDirty('availability') || ! $plate->exists && $plate->getAttribute('availability')) {
                $plate->setAttribute('visible', $plate->getAttribute('availability') === self::AVAILABLE);
            } elseif ($plate->isDirty('visible')) {
                $plate->setAttribute('availability', $plate->getAttribute('visible') ? self::AVAILABLE : self::UNAVAILABLE);
            }
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity', 'unit_price');
    }

    public function ingredientList(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('is_main', 'position')->orderByPivot('position');
    }

    // what the restaurant declares: pivot status contains | may_contain
    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class)->withPivot('status');
    }

    public function dietaryTags(): BelongsToMany
    {
        return $this->belongsToMany(DietaryTag::class)->withPivot('status', 'source');
    }

    // ---------------------------------------------------------------- queries for search and validation

    // can be ordered now: available and in season (month 1-12, default the current one)
    public function scopeOrderable(Builder $query, ?int $month = null): Builder
    {
        $month ??= (int) now()->month;

        return $query->where('availability', self::AVAILABLE)->where(fn ($q) => $q
            ->whereNull('available_from_month')
            ->orWhere(fn ($q) => $q->whereColumn('available_from_month', '<=', 'available_to_month')
                ->where('available_from_month', '<=', $month)->where('available_to_month', '>=', $month))
            ->orWhere(fn ($q) => $q->whereColumn('available_from_month', '>', 'available_to_month')
                ->where(fn ($q) => $q->where('available_from_month', '<=', $month)->orWhere('available_to_month', '>=', $month))));
    }

    public function isOrderable(?int $month = null): bool
    {
        $month ??= (int) now()->month;
        [$from, $to] = [$this->available_from_month, $this->available_to_month];
        $inSeason = $from === null || $to === null
            || ($from <= $to ? $month >= $from && $month <= $to : $month >= $from || $month <= $to);

        return $this->availability === self::AVAILABLE && $inSeason;
    }

    // stated "yes" for EVERY tag code (a missing or "unknown" declaration never matches)
    public function scopeWithDiet(Builder $query, array $codes): Builder
    {
        foreach ($codes as $code) {
            $query->whereHas('dietaryTags', fn ($q) => $q->where('code', $code)->where('dietary_tag_plate.status', DietaryTag::YES));
        }

        return $query;
    }

    // Safely free from EVERY allergen code. All four conditions are needed, otherwise the plate
    // is "not determinable", never "safe":
    // 1. the restaurant declared its allergens as complete;
    // 2. it declares none of them, neither "contains" nor "may_contain" (cross-contact);
    // 3. none of them is derived from its ingredients (reference data);
    // 4. it has ingredients and all of them are verified.
    public function scopeFreeFrom(Builder $query, array $allergenCodes): Builder
    {
        return $query
            ->where('allergen_info', self::ALLERGENS_COMPLETE)
            ->whereDoesntHave('allergens', fn ($q) => $q->whereIn('code', $allergenCodes))
            ->whereDoesntHave('ingredientList', fn ($q) => $q->whereHas('allergens', fn ($q) => $q->whereIn('code', $allergenCodes)))
            ->whereHas('ingredientList')
            ->whereDoesntHave('ingredientList', fn ($q) => $q->where('info_status', '!=', Ingredient::VERIFIED));
    }

    public function scopeWithIngredients(Builder $query, array $names): Builder
    {
        foreach ($names as $name) {
            $query->whereHas('ingredientList', fn ($q) => $q->where('name', Ingredient::normalizeName($name)));
        }

        return $query;
    }

    public function scopeWithoutIngredients(Builder $query, array $names): Builder
    {
        return $query->whereDoesntHave('ingredientList', fn ($q) => $q->whereIn('name', array_map([Ingredient::class, 'normalizeName'], $names)));
    }

    // allergens DERIVED from the ingredients (reference data), separate from the declaration
    public function derivedAllergens(): Collection
    {
        return Allergen::whereHas('ingredients.plates', fn ($q) => $q->where('plates.id', $this->id))->orderBy('annex_number')->get();
    }

    // ---------------------------------------------------------------- writing

    // Replaces the ingredients with the given names (in order; the first ones can be marked as main).
    // Unknown names enter the registry as unverified. Also refreshes the display label.
    public function syncIngredients(array $names, int $mainCount = 0): void
    {
        $names = collect($names)->map(fn ($n) => trim((string) $n))->filter()->unique(fn ($n) => Ingredient::normalizeName($n))->values();
        $pivot = [];
        foreach ($names as $i => $name) {
            $ingredient = Ingredient::firstOrCreate(['name' => Ingredient::normalizeName($name)], ['info_status' => Ingredient::UNVERIFIED]);
            $pivot[$ingredient->id] = ['position' => $i, 'is_main' => $i < $mainCount];
        }
        $this->ingredientList()->sync($pivot);
        $this->forceFill(['ingredients' => $names->implode(', ')])->saveQuietly();
    }

    // Plates are opened by slug in the admin panel, so the slug must be unique (trashed ones too):
    // on collision append the restaurant slug, then a counter.
    public static function uniqueSlug(string $name, ?string $restaurantSlug = null, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '-') ?: 'plate';
        $taken = fn ($slug) => static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();
        $slug = $base;
        if ($taken($slug) && $restaurantSlug) {
            $slug = $base . '-' . $restaurantSlug;
        }
        $stem = $slug;
        $i = 2;
        while ($taken($slug)) {
            $slug = $stem . '-' . $i++;
        }

        return $slug;
    }
}
