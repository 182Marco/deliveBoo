<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers {
        register as registerNewUser;
    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // ?role=restaurateur (partner CTAs) preselects the partner tab; a failed submit keeps its tab.
    // A signed-in customer may open only the partner form: the partner account is a separate one
    // (one role per account), and the customer stays signed in until the form is sent.
    public function showRegistrationForm(Request $request)
    {
        $asked = $request->old('role', $request->query('role'));
        $role = in_array($asked, User::SIGNUP_ROLES, true) ? $asked : User::CUSTOMER;

        if ($away = $this->awayFromSignUp($request, $role)) {
            return $away;
        }

        return view('auth.register', ['role' => $role, 'current' => $request->user()]);
    }

    // a signed-in customer is signed out only once the partner form is valid: a failed submit keeps the session
    public function register(Request $request)
    {
        if ($away = $this->awayFromSignUp($request, $request->input('role'))) {
            return $away;
        }

        if ($request->user()) {
            $this->validator($request->all())->validate();
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->registerNewUser($request);
    }

    // guests may sign up for any public role; a customer only for a partner account; anyone else goes home
    private function awayFromSignUp(Request $request, ?string $role): ?RedirectResponse
    {
        $user = $request->user();
        if (! $user || ($user->role === User::CUSTOMER && $role === User::RESTAURATEUR)) {
            return null;
        }

        return redirect($user->homePath());
    }

    protected function redirectTo(): string
    {
        return $this->guard()->user()?->homePath() ?? '/';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // signed-in users are handled by awayFromSignUp (customers may open the partner sign-up)
        $this->middleware('guest')->except(['showRegistrationForm', 'register']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            // never admin: the role of a public sign-up is customer or restaurateur
            'role' => ['nullable', Rule::in(User::SIGNUP_ROLES)],
            // only partners have a VAT number (11 digits, one account each)
            'vat_number' => ['exclude_unless:role,'.User::RESTAURATEUR, 'required', 'numeric', 'digits_between:11,11', 'unique:users,vat_number'],
        ], $this->signedInCustomerMessages($data));
    }

    // a customer who types the email of their own account gets told why it is taken
    private function signedInCustomerMessages(array $data): array
    {
        $current = $this->guard()->user();
        $same = $current && strcasecmp((string) ($data['email'] ?? ''), (string) $current->email) === 0;

        return $same ? ['email.unique' => __('partner.auth.register.upgrade.sameEmail')] : [];
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = new User([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        // role is never fillable: set here, from the validated whitelist (customer when missing)
        $user->role = ($data['role'] ?? null) === User::RESTAURATEUR ? User::RESTAURATEUR : User::CUSTOMER;
        $user->vat_number = $user->role === User::RESTAURATEUR ? $data['vat_number'] : null;
        $user->save();

        return $user;
    }
}
