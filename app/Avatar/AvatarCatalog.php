<?php

namespace App\Avatar;

/**
 * Server-side view of the svg-avatar package data (packages/svg-avatar): which trait names and values exist.
 * Reads only the package's JSON data, never its logic: the avatar itself is drawn in the browser.
 */
class AvatarCatalog
{
    /** colour trait => palette group, as in packages/svg-avatar/palettes/resolve.js */
    private const COLOR_TRAITS = [
        'bgColor' => 'bg',
        'skinColor' => 'skin',
        'hairColor' => 'hair',
        'clothColor' => 'cloth',
        'accentColor' => 'accent',
        'irisColor' => 'iris',
    ];

    /** @return array<string, list<string>> trait => allowed values */
    public static function options(): array
    {
        return once(function () {
            $parts = self::read('assets/parts.json');
            $palette = self::read('palettes/default.json');

            $shapes = array_map(fn (array $variants) => array_keys($variants), $parts['categories']);
            $colors = array_map(fn (string $group) => array_keys($palette[$group]), self::COLOR_TRAITS);

            return $shapes + $colors;
        });
    }

    /**
     * Override pairs the package rules forbid (packages/svg-avatar/rules/rules.json, "exclude"),
     * e.g. ['accessory' => 'beanie', 'hair' => 'afro']: the drawing would silently drop one of them.
     *
     * @return list<array<string, string>>
     */
    public static function excludedPairs(): array
    {
        return once(function () {
            $pairs = [];
            foreach (self::read('rules/rules.json')['exclude'] as $rule) {
                unset($rule['prefer']);
                [$first, $second] = array_keys($rule);
                foreach ($rule[$first] as $a) {
                    foreach ($rule[$second] as $b) {
                        $pairs[] = [$first => $a, $second => $b];
                    }
                }
            }

            return $pairs;
        });
    }

    /** problems of a set of overrides (empty list = valid): unknown traits or values, forbidden pairs */
    public static function overrideErrors(array $overrides): array
    {
        $options = self::options();
        $errors = [];
        foreach ($overrides as $trait => $value) {
            if (! is_string($trait) || ! isset($options[$trait])) {
                $errors[] = "unknown trait $trait";
            } elseif (! is_string($value) || ! in_array($value, $options[$trait], true)) {
                $errors[] = "unknown value for $trait";
            }
        }
        if ($errors !== []) {
            return $errors; // only plain known values reach the pair check
        }
        foreach (self::excludedPairs() as $pair) {
            if (array_intersect_assoc($pair, $overrides) === $pair) {
                $errors[] = 'forbidden combination '.implode(' + ', $pair);
            }
        }

        return $errors;
    }

    /** keeps only known traits with allowed string values */
    public static function sanitizeOverrides(mixed $overrides): array
    {
        if (! is_array($overrides)) {
            return [];
        }
        $options = self::options();

        return array_filter(
            $overrides,
            fn ($value, $trait) => is_string($value) && in_array($value, $options[$trait] ?? [], true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /** a new random seed: 16 hex characters, like randomSeed() in the package */
    public static function randomSeed(): string
    {
        return bin2hex(random_bytes(8));
    }

    private static function read(string $file): array
    {
        return json_decode(file_get_contents(base_path('packages/svg-avatar/'.$file)), true, flags: JSON_THROW_ON_ERROR);
    }
}
