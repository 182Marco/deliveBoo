<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'description', 'price_range', 'phone', 'city', 'address', 'img'])]
class Restaurant extends Model
{
    // restaurants are opened by slug (site and admin): on collision append a counter
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '-') ?: 'restaurant';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'delivery_fee' => 'float',
            'min_order' => 'float',
            'accepting_orders' => 'boolean',
            // aggregates of the reviews, written only by App\Reviews\RestaurantRating
            'rating_avg' => 'float',
            'rating_count' => 'integer',
            'rating_score' => 'float',
        ];
    }

    // "Guest favourites": Bayesian score first (a few 5-star reviews do not beat hundreds of good ones)
    public function scopeTopRated(Builder $query): Builder
    {
        return $query->whereNotNull('rating_score')
            ->orderByDesc('rating_score')->orderByDesc('rating_count')->orderBy('id');
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(OpeningHour::class)->orderBy('weekday')->orderBy('opens_at');
    }

    public function plates(): HasMany
    {
        return $this->hasMany(Plate::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class);
    }
}
