<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// The logged-in account for the Vue site (Sanctum SPA mode: same session cookie as the web pages).
// A guest gets { user: null } with 200, so the site can ask on every load without console errors;
// a banned account is logged out by the "active" middleware (403).
class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['user' => $user ? self::payload($user) : null]);
    }

    /** only what the header and the checkout need: never password, tokens, Google id or ban data */
    public static function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            // where the account menu sends partners and admins (the site is the customers' home)
            'home' => $user->homePath(),
            // Google photo when there is one, otherwise the svg-avatar of the seed
            'avatar_url' => $user->avatar_url,
            'avatar_seed' => $user->avatarSeed(),
            'avatar_overrides' => (object) $user->avatarOverrides(),
        ];
    }
}
