<?php

namespace App\Http\Requests\Platform;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

// "New account" of the platform backoffice: a customer or a restaurateur, never an admin
// (admins come only from DemoAccountsSeeder or `php artisan foodsy:make-admin`)
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(User::SIGNUP_ROLES)],
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            // like the public sign-up: only partners have a VAT number, 11 digits, one account each
            'vat_number' => ['exclude_unless:role,'.User::RESTAURATEUR, 'required', 'numeric', 'digits_between:11,11', 'unique:users,vat_number'],
        ];
    }
}
