<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// role:restaurateur, role:admin … — each area of the app is open only to its roles.
// Hiding a link is not a protection: every route group declares its roles here.
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $request->expectsJson()
                ? response()->json(['message' => __('auth.forbidden')], 401)
                : redirect()->guest(route('login'));
        }

        if (! $user->hasRole(...$roles)) {
            return $request->expectsJson()
                ? response()->json(['message' => __('auth.forbidden')], 403)
                : redirect($user->homePath())->with('status', __('auth.forbidden'));
        }

        return $next($request);
    }
}
