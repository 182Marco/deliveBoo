<?php

namespace App\Carts;

use App\Cart;
use App\CartItem;
use App\Plate;
use App\Restaurant;
use App\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

// The single place that reads and writes carts (dev/PIANO_CARRELLO.md).
// Owner of a cart: the logged-in account, otherwise the guest token of the cookie (stored as SHA-256).
// Every write runs in a transaction with the cart row locked, then recomputes restaurant and version:
// concurrent requests (two tabs, double clicks) are applied one after the other, never interleaved.
class CartService
{
    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /** the cart of the owner, without creating it */
    public function find(?User $user, ?string $token): ?Cart
    {
        if ($user !== null) {
            return Cart::where('user_id', $user->id)->first();
        }

        return $token ? Cart::where('guest_token', self::hashToken($token))->first() : null;
    }

    /** the cart of the owner, created on the first write (two concurrent creations: the unique index picks one) */
    public function findOrCreate(?User $user, string $token): Cart
    {
        $owner = $user !== null ? ['user_id' => $user->id] : ['guest_token' => self::hashToken($token)];

        $cart = Cart::where($owner)->first();
        if ($cart !== null) {
            return $cart;
        }

        try {
            return Cart::forceCreate($owner);
        } catch (UniqueConstraintViolationException) {
            return Cart::where($owner)->firstOrFail();
        }
    }

    /**
     * Absolute number of portions of a plate (0 removes the line).
     * Lowering or removing is always allowed; raising needs an orderable plate of the cart's restaurant,
     * unless $replace empties a cart of another restaurant first.
     *
     * @throws CartException
     */
    public function setQuantity(Cart $cart, Plate $plate, int $quantity, bool $replace = false): Cart
    {
        return $this->write($cart, function (Cart $cart) use ($plate, $quantity, $replace) {
            $line = $cart->items()->where('plate_id', $plate->id)->first();
            $current = $line?->quantity ?? 0;

            if ($quantity === 0) {
                $line?->delete();

                return;
            }

            if ($quantity > $current) {
                $this->assertCanAdd($cart, $plate, $replace);
                if ($replace && $cart->restaurant_id !== null && $cart->restaurant_id !== $plate->restaurant_id) {
                    $cart->items()->delete();
                    $line = null;
                    $current = 0;
                }
                $others = (int) $cart->items()->where('plate_id', '!=', $plate->id)->sum('quantity');
                if ($others + $quantity > config('cart.max_portions')) {
                    throw new CartException(CartException::TOO_MANY, 422, __('api.cart.tooMany', ['max' => config('cart.max_portions')]));
                }
            }

            if ($line !== null) {
                $line->forceFill(['quantity' => $quantity])->save();
            } else {
                $cart->items()->create(['plate_id' => $plate->id, 'quantity' => $quantity]);
            }
        });
    }

    public function clear(Cart $cart): Cart
    {
        return $this->write($cart, fn (Cart $cart) => $cart->items()->delete());
    }

    /** after a paid order: empties the cart of the account, or of the guest cookie (nothing to do without one) */
    public function clearFor(?User $user, ?string $token): void
    {
        $cart = $this->find($user, $token);
        if ($cart !== null) {
            $this->clear($cart);
        }
    }

    /**
     * One-off import of the cart kept in localStorage by the old site (step 6), only into an empty cart.
     * Best effort: unknown or not orderable plates are skipped, only the restaurant of the first valid
     * plate is kept, quantities are clamped to the limits.
     *
     * @param  array<int, array{plate_id:int, quantity:int}>  $items
     */
    public function import(Cart $cart, array $items): Cart
    {
        return $this->write($cart, function (Cart $cart) use ($items) {
            if ($cart->items()->exists()) {
                return;
            }
            $quantities = collect($items)->groupBy('plate_id')->map(fn ($lines) => (int) $lines->sum('quantity'));
            $plates = Plate::whereIn('id', $quantities->keys())->get()->keyBy('id');
            $restaurantId = null;
            $total = 0;
            foreach ($quantities as $plateId => $quantity) {
                $plate = $plates->get($plateId);
                if ($plate === null || ! $plate->isOrderable() || $this->restaurantRefusesOrders($plate->restaurant_id)) {
                    continue;
                }
                $restaurantId ??= $plate->restaurant_id;
                $quantity = min($quantity, config('cart.max_quantity'), config('cart.max_portions') - $total);
                if ($plate->restaurant_id !== $restaurantId || $quantity < 1) {
                    continue;
                }
                $cart->items()->create(['plate_id' => $plate->id, 'quantity' => $quantity]);
                $total += $quantity;
            }
        });
    }

    /**
     * Login after shopping as a guest (dev/PIANO_CARRELLO.md, Fase 3): "the recent guest cart wins,
     * the same restaurant merges". Idempotent: afterwards the guest cart no longer exists.
     *  - guest cart empty or missing   → the account keeps its cart
     *  - account cart empty or missing → the guest cart becomes the account's
     *  - same restaurant               → union of the lines, quantity = max of the two
     *  - other restaurant              → the guest cart replaces the account's (notice "replaced")
     *  - demo account (shared by many visitors) → always the guest cart, even empty, never a notice
     * Plates no longer orderable are dropped (notice "removed").
     */
    public function merge(User $user, ?string $token): void
    {
        DB::transaction(function () use ($user, $token) {
            $guest = $token ? Cart::where('guest_token', self::hashToken($token))->lockForUpdate()->first() : null;
            $mine = Cart::where('user_id', $user->id)->lockForUpdate()->first();
            $demo = $user->isDemoAccount();

            $guestLines = $guest ? $this->orderableLines($guest) : ['lines' => collect(), 'removed' => []];
            $guestEmpty = $guestLines['lines']->isEmpty();
            $mineEmpty = $mine === null || ! $mine->items()->exists();

            if ($guestEmpty && ! $demo) {
                $guest?->delete();

                return;
            }

            $notices = [];
            $sameRestaurant = ! $mineEmpty && ! $guestEmpty && $mine->restaurant_id === $guestLines['restaurant_id'];
            if ($sameRestaurant) {
                $this->mergeLines($mine, $guestLines['lines']);
                $guest->delete();
                $target = $mine;
                $notices[] = ['code' => 'merged', 'params' => []];
            } else {
                if (! $mineEmpty && ! $demo) {
                    $notices[] = ['code' => 'replaced', 'params' => ['restaurant' => (string) $mine->restaurant?->name]];
                }
                $mine?->delete();
                $target = $guest ?? Cart::forceCreate(['user_id' => $user->id]);
                $target->forceFill(['user_id' => $user->id, 'guest_token' => null])->save();
                $target->items()->whereNotIn('plate_id', $guestLines['lines']->keys())->delete();
            }

            if ($guestLines['removed'] && ! $demo) {
                $notices[] = ['code' => 'removed', 'params' => ['plates' => implode(', ', $guestLines['removed'])]];
            }
            $this->write($target, fn () => null);
            if ($notices) {
                $target->refresh()->forceFill(['notice' => $notices])->saveQuietly();
            }
        });
    }

    /** returns the notices waiting on the cart and removes them (shown once) */
    public function pullNotices(?Cart $cart): array
    {
        if ($cart === null || empty($cart->notice)) {
            return [];
        }
        $notices = $cart->notice;
        $cart->forceFill(['notice' => null])->saveQuietly();

        return $notices;
    }

    /** what the site shows: lines with the current prices and availability of the plates */
    public function snapshot(?Cart $cart, bool $forUser): array
    {
        $items = $cart?->items()->with(['plate.restaurant:id,slug,name'])->get() ?? collect();
        $lines = $items->map(fn (CartItem $item) => [
            'plate_id' => $item->plate_id,
            'quantity' => $item->quantity,
            'line_total' => round($item->quantity * $item->plate->price, 2),
            'plate' => [
                'id' => $item->plate->id,
                'name' => $item->plate->name,
                'price' => $item->plate->price,
                'img' => $item->plate->img,
                'orderable' => ! $item->plate->trashed() && $item->plate->isOrderable(),
                'restaurant_id' => $item->plate->restaurant_id,
                'restaurant_slug' => $item->plate->restaurant?->slug,
            ],
        ])->values();
        $restaurant = $lines->isEmpty() ? null : $items->first()->plate->restaurant;

        return [
            'owner' => $forUser ? 'user' : 'guest',
            'version' => $cart?->version ?? 0,
            'restaurant' => $restaurant ? $restaurant->only(['id', 'slug', 'name']) : null,
            'items' => $lines->all(),
            'count' => (int) $lines->sum('quantity'),
            'total' => round($lines->sum('line_total'), 2),
        ];
    }

    /** lines of a cart that can still be ordered ([plate_id => quantity]), and the names of the others */
    private function orderableLines(Cart $cart): array
    {
        $items = $cart->items()->with('plate')->get();
        [$ok, $gone] = $items->partition(fn (CartItem $i) => ! $i->plate->trashed() && $i->plate->isOrderable()
            && ! $this->restaurantRefusesOrders($i->plate->restaurant_id));

        return [
            'lines' => $ok->mapWithKeys(fn (CartItem $i) => [$i->plate_id => $i->quantity]),
            'removed' => $gone->map(fn (CartItem $i) => $i->plate->name)->values()->all(),
            'restaurant_id' => $ok->first()?->plate->restaurant_id,
        ];
    }

    /** same restaurant: the larger quantity of each plate, within the limits of a cart */
    private function mergeLines(Cart $cart, $guestLines): void
    {
        $current = $cart->items()->pluck('quantity', 'plate_id');
        $total = (int) $current->sum();
        foreach ($guestLines as $plateId => $quantity) {
            $had = (int) ($current[$plateId] ?? 0);
            $wanted = min(max($had, $quantity), config('cart.max_quantity'));
            $wanted = min($wanted, $had + config('cart.max_portions') - $total);
            if ($wanted <= $had) {
                continue;
            }
            $cart->items()->updateOrCreate(['plate_id' => $plateId], ['quantity' => $wanted]);
            $total += $wanted - $had;
        }
    }

    /** runs $change on the locked cart, then recomputes its restaurant and bumps the version */
    private function write(Cart $cart, callable $change): Cart
    {
        return DB::transaction(function () use ($cart, $change) {
            $locked = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $change($locked);
            $first = $locked->items()->first();
            $locked->restaurant_id = $first ? Plate::withTrashed()->whereKey($first->plate_id)->value('restaurant_id') : null;
            $locked->version++;
            $locked->save();

            return $locked;
        });
    }

    /** @throws CartException */
    private function assertCanAdd(Cart $cart, Plate $plate, bool $replace): void
    {
        if ($plate->trashed() || ! $plate->isOrderable()) {
            throw new CartException(CartException::UNAVAILABLE, 422, __('api.cart.unavailable', ['plate' => $plate->name]));
        }
        if ($this->restaurantRefusesOrders($plate->restaurant_id)) {
            throw new CartException(CartException::NOT_ACCEPTING, 422, __('api.order.notAccepting'));
        }
        if (! $replace && $cart->restaurant_id !== null && $cart->restaurant_id !== $plate->restaurant_id) {
            throw new CartException(CartException::OTHER_RESTAURANT, 409, __('api.cart.otherRestaurant'));
        }
    }

    // the restaurants of a banned restaurateur stay visible but take no orders (as in OrderController)
    private function restaurantRefusesOrders(int $restaurantId): bool
    {
        return Restaurant::whereKey($restaurantId)->whereHas('user', fn ($u) => $u->whereNotNull('banned_at'))->exists();
    }
}
