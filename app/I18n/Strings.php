<?php

namespace App\I18n;

/**
 * Every static string of Foodsy lives in ONE object: resources/js/utils/strings.json.
 * The Vue site imports it (resources/js/utils/i18n.js), Laravel reads it through StringsLoader,
 * so __('partner.common.save') and t("site.cart.title") come from the same file.
 *
 * Shape: { "locales": {code: {short, name}}, "<group>": { ...nested keys... } }, where a leaf is
 * { "en": "...", "it": "...", "de": "...", "fr": "...", "es": "..." }. English is the default
 * and the fallback: a leaf without a locale falls back to English.
 */
class Strings
{
    public const DEFAULT_LOCALE = 'en';

    // shared by the Vue site (document.cookie) and the partner hub (LocaleController): not encrypted
    public const COOKIE = 'foodsy_locale';

    private static ?array $data = null;

    public static function path(): string
    {
        return resource_path('js/utils/strings.json');
    }

    public static function all(): array
    {
        return self::$data ??= json_decode(file_get_contents(self::path()), true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return array<string, array{short: string, name: string}> code => labels, in menu order */
    public static function locales(): array
    {
        return self::all()['locales'];
    }

    public static function supports(mixed $locale): bool
    {
        return is_string($locale) && isset(self::locales()[$locale]);
    }

    /** one translation group (top-level key) in one locale, null when the group is not here */
    public static function group(string $group, string $locale): ?array
    {
        $node = self::all()[$group] ?? null;

        return $group !== 'locales' && is_array($node) ? self::resolve($node, $locale) : null;
    }

    public static function isLeaf(mixed $node): bool
    {
        return is_array($node) && is_string($node[self::DEFAULT_LOCALE] ?? null);
    }

    // leaves become the string of $locale; a missing one is left out, so the translator uses English
    private static function resolve(array $node, string $locale): array
    {
        $lines = [];
        foreach ($node as $key => $child) {
            if (self::isLeaf($child)) {
                if (isset($child[$locale])) {
                    $lines[$key] = $child[$locale];
                }
            } elseif (is_array($child)) {
                $lines[$key] = self::resolve($child, $locale);
            }
        }

        return $lines;
    }
}
