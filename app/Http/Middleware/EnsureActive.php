<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// A banned account loses its session on the very next request, wherever it is:
// the ban is enforced here, on the server, not by hiding parts of the interface.
class EnsureActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->isBanned()) {
            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return $request->expectsJson()
                ? response()->json(['message' => __('auth.banned')], 403)
                : redirect()->route('login')->withErrors(['email' => __('auth.banned')]);
        }

        return $next($request);
    }
}
