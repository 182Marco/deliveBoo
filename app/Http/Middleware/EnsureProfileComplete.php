<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// A restaurateur who signed up with Google has no VAT number yet: the partner hub waits until it is
// added on /account (the public sign-up form already requires it).
class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->role === User::RESTAURATEUR && $user->vat_number === null) {
            return $request->expectsJson()
                ? response()->json(['message' => __('partner.account.completeLead')], 403)
                : redirect()->route('account');
        }

        return $next($request);
    }
}
