<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

/**
 * "Continue with Google" (dev/PIANO_AUTH_CLIENTI.md, step 6.1). Rules:
 * - the OAuth state lives in the session (never stateless): a forged or replayed callback fails;
 * - the Google email must be verified; the stable key is the Google id ("sub"), never the email;
 * - an email that already has an account is NOT linked automatically: the owner logs in with the
 *   password and links Google from /account (no account takeover through a Google address);
 * - the role chosen on the sign-up tab applies only to new accounts; existing roles never change;
 * - admins never use Google; banned accounts are refused.
 */
class GoogleController extends Controller
{
    private const MODE = 'google.mode';
    private const ROLE = 'google.role';

    public static function enabled(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    /** guests: sign in or sign up (?role=customer|restaurateur, only for new accounts) */
    public function redirect(Request $request): RedirectResponse
    {
        abort_unless(self::enabled(), 404);
        $role = in_array($request->query('role'), User::SIGNUP_ROLES, true) ? $request->query('role') : User::CUSTOMER;
        $request->session()->put([self::MODE => 'login', self::ROLE => $role]);

        return Socialite::driver('google')->scopes(['openid', 'email', 'profile'])->redirect();
    }

    /** logged-in users: link Google to the current account (started from /account) */
    public function link(Request $request): RedirectResponse
    {
        abort_unless(self::enabled(), 404);
        if ($request->user()->isAdmin()) {
            return redirect()->route('account')->withErrors(['google' => __('partner.auth.google.admin')]);
        }
        $request->session()->put(self::MODE, 'link');

        return Socialite::driver('google')->scopes(['openid', 'email', 'profile'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(self::enabled(), 404);
        $mode = $request->session()->pull(self::MODE, 'login');
        $role = $request->session()->pull(self::ROLE, User::CUSTOMER);
        $back = $mode === 'link' && $request->user() ? route('account') : route('login');

        try {
            $google = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect($back)->withErrors(['google' => __('partner.auth.google.state')]);
        } catch (Throwable $e) {
            report($e);

            return redirect($back)->withErrors(['google' => __('partner.auth.google.failed')]);
        }

        $id = (string) $google->getId();
        $email = strtolower((string) $google->getEmail());
        if ($id === '' || $email === '' || ($google->user['email_verified'] ?? false) !== true) {
            return redirect($back)->withErrors(['google' => __('partner.auth.google.unverified')]);
        }

        return $mode === 'link' && $request->user()
            ? $this->linkTo($request, $request->user(), $id, $google->getAvatar())
            : $this->signIn($request, $id, $email, $google, $role);
    }

    private function linkTo(Request $request, User $user, string $id, ?string $avatar): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->route('account')->withErrors(['google' => __('partner.auth.google.admin')]);
        }
        if (User::where('google_id', $id)->whereKeyNot($user->id)->exists()) {
            return redirect()->route('account')->withErrors(['google' => __('partner.auth.google.taken')]);
        }
        $user->google_id = $id;
        $user->avatar_url = $avatar ?: $user->avatar_url;
        $user->save();

        return redirect()->route('account')->with('status', __('partner.auth.google.linked'));
    }

    private function signIn(Request $request, string $id, string $email, $google, string $role): RedirectResponse
    {
        $user = User::where('google_id', $id)->first();

        if ($user === null) {
            // an existing account is never taken over through its email: password first, then link
            if (User::where('email', $email)->exists()) {
                return redirect()->route('login')->withInput(['email' => $email])->withErrors(['google' => __('partner.auth.google.exists')]);
            }
            $user = new User([
                'name' => $google->user['given_name'] ?? (explode(' ', (string) $google->getName())[0] ?: 'Foodsy'),
                'last_name' => $google->user['family_name'] ?? (trim(strstr((string) $google->getName(), ' ') ?: '') ?: '-'),
                'email' => $email,
            ]);
            // role from the whitelist of the sign-up tab, never admin; a restaurateur completes the VAT number at /account
            $user->role = in_array($role, User::SIGNUP_ROLES, true) ? $role : User::CUSTOMER;
            $user->google_id = $id;
            $user->avatar_url = $google->getAvatar();
            $user->email_verified_at = now();
            $user->save();
        }

        if ($user->isAdmin()) {
            return redirect()->route('login')->withErrors(['google' => __('partner.auth.google.admin')]);
        }
        if ($user->isBanned()) {
            return redirect()->route('login')->withErrors(['email' => __('auth.banned')]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended($user->homePath());
    }
}
