<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\GoogleController;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// "Your account" for every role: Google link status, link / unlink, and the VAT number a
// restaurateur created through Google must add before opening the partner hub.
class AccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('account.show', ['user' => $request->user(), 'googleEnabled' => GoogleController::enabled()]);
    }

    public function completeProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->role === User::RESTAURATEUR && $user->vat_number === null, 403);
        $data = $request->validate(['vat_number' => ['required', 'numeric', 'digits_between:11,11', 'unique:users,vat_number']]);
        $user->vat_number = $data['vat_number'];
        $user->save();

        return redirect()->route('admin.home')->with('status', __('partner.account.saved'));
    }

    public function unlinkGoogle(Request $request): RedirectResponse
    {
        $user = $request->user();
        // without a password an account unlinked from Google could not sign in any more
        if ($user->password === null) {
            return back()->withErrors(['google' => __('partner.auth.google.needPassword')]);
        }
        $user->google_id = null;
        $user->avatar_url = null;
        $user->save();

        return back()->with('status', __('partner.auth.google.unlinked'));
    }
}
