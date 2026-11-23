<?php

namespace App\Http\Requests\Platform;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// "Edit account": personal data only. Role, password, id, Google link and ban are never part of
// this form (UserPolicy also refuses admins, demo accounts and the admin's own account).
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'vat_number' => $user->role === User::RESTAURATEUR
                ? ['required', 'numeric', 'digits_between:11,11', Rule::unique('users', 'vat_number')->ignore($user->id)]
                : ['exclude'],
        ];
    }
}
