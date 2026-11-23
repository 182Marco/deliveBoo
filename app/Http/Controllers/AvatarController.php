<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Customise avatar" page of the logged-in user, every role (/account/avatar). The editor itself is
 * the svg-avatar package (resources/js/jsBack/avatarEditor.js mounts it); here: authorization,
 * validation, storage. Only $request->user() is ever read or written: no id in the routes.
 * The page opens in the frame of the user's area: the backoffice for admins, the signed-in
 * frame (layouts.app, role-aware) for restaurateurs and customers.
 */
class AvatarController extends Controller
{
    private const RETURN_KEY = 'avatar.return';

    // paths that are never a place to go back to after saving
    private const NOT_A_RETURN = ['/account/avatar', '/admin/avatar', '/api', '/login', '/logout', '/register', '/password', '/auth', '/locale'];

    public function edit(Request $request): View
    {
        $request->session()->put(self::RETURN_KEY, $this->safeReturnUrl($request));
        $user = $request->user();

        // after a failed submit, reopen the editor on what the user had composed
        $value = $request->old('seed') !== null
            ? ['seed' => (string) $request->old('seed'), 'overrides' => json_decode((string) $request->old('overrides'), true) ?: (object) []]
            : ['seed' => $user->avatarSeed(), 'overrides' => $user->avatarOverrides() ?: (object) []];

        return view('account.avatar', [
            'layout' => $user->isAdmin() ? 'layouts.platform' : 'layouts.app',
            'value' => $value,
            'labels' => __('partner.avatar.editor'),
            'returnUrl' => $request->session()->get(self::RETURN_KEY),
        ]);
    }

    public function update(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->avatar = ['seed' => $request->validated('seed'), 'overrides' => $request->overrides()];
        $user->save();

        $return = $request->session()->pull(self::RETURN_KEY, $this->home($user));

        return redirect()->to($return)->with('avatarSaved', true);
    }

    private function home(User $user): string
    {
        return url($user->homePath());
    }

    /**
     * where to go back after saving: the previous page of this site when it belongs to the user's own
     * area (partner hub for restaurateurs, backoffice for admins, the site for customers), never an
     * external URL; otherwise the home of the role
     */
    private function safeReturnUrl(Request $request): string
    {
        $user = $request->user();
        $fallback = $this->home($user);
        $previous = url()->previous();
        $parts = parse_url($previous);
        $path = $parts['path'] ?? '';

        $sameSite = isset($parts['scheme'], $parts['host'])
            && $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '') === $request->getSchemeAndHttpHost();
        $under = fn (string $prefix) => $path === $prefix || str_starts_with($path, $prefix.'/');
        $excluded = collect(self::NOT_A_RETURN)->contains(fn ($prefix) => $under($prefix));
        $ownArea = match (true) {
            $under('/admin') => $user->hasRole(User::RESTAURATEUR),
            $under('/platform') => $user->isAdmin(),
            default => true,
        };

        return $sameSite && $ownArea && ! $excluded && $path !== '' && ! str_contains($path, '//') ? $previous : $fallback;
    }
}
