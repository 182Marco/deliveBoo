<?php

namespace App\Listeners;

use App\Carts\CartService;
use Illuminate\Auth\Events\Login;

// Every login (password, Google, sign-up, remember-me) brings the cart of the guest cookie into the
// account (rules in CartService::merge, dev/PIANO_CARRELLO.md). Registered by Laravel's event discovery.
class MergeGuestCart
{
    public function __construct(private CartService $carts)
    {
    }

    public function handle(Login $event): void
    {
        if (! config('cart.server')) {
            return;
        }

        $token = request()->cookie(config('cart.cookie'));
        $this->carts->merge($event->user, is_string($token) ? $token : null);
    }
}
