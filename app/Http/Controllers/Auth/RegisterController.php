<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
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

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // ?role=restaurateur (partner CTAs) preselects the partner tab; a failed submit keeps its tab
    public function showRegistrationForm(Request $request)
    {
        $asked = $request->old('role', $request->query('role'));
        $role = in_array($asked, User::SIGNUP_ROLES, true) ? $asked : User::CUSTOMER;

        return view('auth.register', compact('role'));
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
        $this->middleware('guest');
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
        ]);
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
