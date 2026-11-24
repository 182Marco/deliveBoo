<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// A customer's review of a delivered order: stars 1-5 and an optional text. Author, order, restaurant,
// is_current, reply and moderation are not fillable: they are written only by the controllers and by
// App\Reviews\RestaurantRating, which also keeps the averages on the restaurant up to date.
#[Fillable(['rating', 'comment'])]
class Review extends Model
{
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_current' => 'boolean',
            'replied_at' => 'datetime',
            'hidden_at' => 'datetime',
        ];
    }

    // not hidden by the moderation
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNull('hidden_at');
    }

    // the reviews that make the average of a restaurant: visible and the latest of their author
    public function scopeCounted(Builder $query): Builder
    {
        return $query->whereNull('hidden_at')->where('is_current', true);
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }

    /** what the author sees in "My orders" */
    public function forAuthor(): array
    {
        return [
            'rating' => $this->rating,
            'comment' => $this->comment,
            'reply' => $this->isHidden() ? null : $this->reply,
            'replied_at' => $this->isHidden() ? null : $this->replied_at?->toIso8601String(),
            'hidden' => $this->isHidden(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }
}
