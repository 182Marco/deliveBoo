<?php

namespace App\Http\Controllers\Auth;

use App\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    // one login page for every role: the account decides where it lands (User::homePath)
    protected function redirectTo(): string
    {
        return $this->guard()->user()?->homePath() ?? '/';
    }

    // right credentials of a banned account: no session is kept and the reason is shown
    // (only after the password matched, so the form does not reveal which emails are banned)
    protected function authenticated(Request $request, $user)
    {
        if ($user->isBanned()) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([$this->username() => [__('auth.banned')]]);
        }

        // every access to the platform backoffice leaves a trace
        if ($user->isAdmin()) {
            AuditLog::record(AuditLog::ADMIN_LOGIN, $user, actor: $user);
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
