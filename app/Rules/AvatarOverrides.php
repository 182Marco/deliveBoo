<?php

namespace App\Rules;

use App\Avatar\AvatarCatalog;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The "overrides" field of the avatar editor: a JSON object whose keys and values exist in the
 * svg-avatar package data, with no forbidden pair written in full (see AvatarCatalog).
 * Single traits and the rules are enough: the package normalizes every drawing, so a valid set of
 * overrides always gives a valid avatar and every saved choice is kept.
 */
class AvatarOverrides implements ValidationRule
{
    public const MAX_TRAITS = 14;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decoded = is_string($value) ? json_decode($value, true) : null;
        $isObject = is_string($value) && str_starts_with(ltrim($value), '{');

        if (! is_array($decoded) || ! $isObject
            || count($decoded) > self::MAX_TRAITS
            || AvatarCatalog::overrideErrors($decoded) !== []) {
            $fail(__('partner.avatar.invalid'));
        }
    }
}
