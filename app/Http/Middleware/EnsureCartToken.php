<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

// Routes /api/cart* (dev/PIANO_CARRELLO.md): 404 while the server cart is switched off (config cart.server),
// otherwise makes sure the browser has its anonymous cart id. The token is random, read by the
// controller from the request attributes, and sent back at every answer so its 400 days restart.
// The cookie is HttpOnly (the site never reads it) and encrypted by EncryptCookies like the session.
class EnsureCartToken
{
    public const ATTRIBUTE = 'cart_token';

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('cart.server'), 404);

        $token = $request->cookie(config('cart.cookie'));
        if (! is_string($token) || strlen($token) !== 40) {
            $token = Str::random(40);
        }
        $request->attributes->set(self::ATTRIBUTE, $token);

        $response = $next($request);
        $response->headers->setCookie(new Cookie(
            config('cart.cookie'),
            $token,
            now()->addMinutes(config('cart.cookie_minutes')),
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            Cookie::SAMESITE_LAX,
        ));

        return $response;
    }
}
