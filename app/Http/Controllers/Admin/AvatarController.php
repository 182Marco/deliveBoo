<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAvatarRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Customise avatar" page of the logged-in user. The editor itself is the svg-avatar package
 * (resources/js/jsBack/avatarEditor.js mounts it); here: authorization, validation, storage.
 * Only $request->user() is ever read or written: no id in the routes.
 */
class AvatarController extends Controller
{
    private const RETURN_KEY = 'avatar.return';

    public function edit(Request $request): View
    {
        $request->session()->put(self::RETURN_KEY, $this->safeReturnUrl($request));
        $user = $request->user();

        // after a failed submit, reopen the editor on what the user had composed
        $value = $request->old('seed') !== null
            ? ['seed' => (string) $request->old('seed'), 'overrides' => json_decode((string) $request->old('overrides'), true) ?: (object) []]
            : ['seed' => $user->avatarSeed(), 'overrides' => $user->avatarOverrides() ?: (object) []];

        return view('admin.avatar.edit', [
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

        $return = $request->session()->pull(self::RETURN_KEY, route('admin.restaurants.index'));

        return redirect()->to($return)->with('avatarSaved', true);
    }

    /** where to go back after saving: the previous partner page of this site, never an external URL */
    private function safeReturnUrl(Request $request): string
    {
        $fallback = route('admin.restaurants.index');
        $previous = url()->previous();
        $parts = parse_url($previous);
        $path = $parts['path'] ?? '';

        $sameSite = isset($parts['scheme'], $parts['host'])
            && $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '') === $request->getSchemeAndHttpHost();
        $partnerPage = ($path === '/admin' || str_starts_with($path, '/admin/')) && ! str_starts_with($path, '/admin/avatar');

        return $sameSite && $partnerPage && ! str_contains($path, '//') ? $previous : $fallback;
    }
}
