<?php

namespace App\Http\Requests;

use App\Rules\AvatarOverrides;
use Illuminate\Foundation\Http\FormRequest;

/** Avatar editor submit: { seed, overrides (JSON) } of the logged-in user, never trusted as sent. */
class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'seed' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{1,64}$/'],
            'overrides' => ['required', 'string', 'max:2000', new AvatarOverrides],
        ];
    }

    public function messages(): array
    {
        return [
            'seed.*' => __('partner.avatar.invalid'),
            'overrides.*' => __('partner.avatar.invalid'),
        ];
    }

    /** validated overrides as an array */
    public function overrides(): array
    {
        return json_decode($this->validated('overrides'), true);
    }
}
