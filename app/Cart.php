<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// The cart of an account (user_id) or of a guest (guest_token = SHA-256 of the cookie token).
// Nothing is fillable: owner, restaurant, version and notice are written only by
// App\Carts\CartService (dev/PIANO_CARRELLO.md), the single place that changes a cart.
class Cart extends Model
{
    use MassPrunable;

    /** guest carts untouched for this many days are deleted (an account cart stays while it has lines) */
    public const GUEST_DAYS = 30;

    /**
     * Abandoned carts, deleted daily by `model:prune` (routes/console.php): guest carts inactive for
     * GUEST_DAYS days, and empty carts of anyone after one day (they are created again at the next write).
     * Lines go with their cart (cascade).
     */
    public function prunable(): Builder
    {
        return static::where(fn (Builder $q) => $q
            ->where(fn (Builder $q) => $q->whereNotNull('guest_token')->where('updated_at', '<', now()->subDays(self::GUEST_DAYS)))
            ->orWhere(fn (Builder $q) => $q->whereDoesntHave('items')->where('updated_at', '<', now()->subDay())));
    }

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'notice' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    // in the order the plates were added
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class)->orderBy('id');
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }
}
