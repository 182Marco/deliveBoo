<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords {
        resetPassword as private saveNewPassword;
    }

    // a reset token for a demo account (created before the demo was on) cannot change its password
    protected function resetPassword($user, $password)
    {
        if ($user->isDemoAccount()) {
            throw ValidationException::withMessages(['email' => [__('passwords.demo')]]);
        }

        $this->saveNewPassword($user, $password);
    }

    // after a reset the account lands on the home of its role
    protected function redirectTo(): string
    {
        return auth()->user()?->homePath() ?? '/';
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
}
