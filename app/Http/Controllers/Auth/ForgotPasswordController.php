<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails {
        sendResetLinkEmail as private sendResetLink;
    }

    // the demo accounts of the portfolio keep the password shown on the login page
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        if (User::where('email', $request->input('email'))->first()?->isDemoAccount()) {
            return back()->withInput($request->only('email'))->withErrors(['email' => __('passwords.demo')]);
        }

        return $this->sendResetLink($request);
    }
}
