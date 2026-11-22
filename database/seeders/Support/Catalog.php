<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Str;

// Read-only access to the data files of database/seeders/data (loaded once per process).
final class Catalog
{
    private static array $cache = [];

    // tier of the recipes a restaurant segment uses
    public const TIERS = [
        'budget' => 'base', 'fast_food' => 'base', 'street_food' => 'base',
        'trattoria' => 'mid', 'traditional' => 'mid', 'ethnic' => 'mid',
        'premium' => 'high', 'fine_dining' => 'high', 'starred' => 'high',
    ];

    public const PRICE_RANGE = [
        'budget' => 1, 'fast_food' => 1, 'street_food' => 1, 'trattoria' => 2, 'traditional' => 2,
        'ethnic' => 2, 'premium' => 3, 'fine_dining' => 4, 'starred' => 4,
    ];

    public static function data(string $file): array
    {
        return self::$cache[$file] ??= require dirname(__DIR__) . "/data/{$file}.php";
    }

    /** @return array<string, array> cuisine key => file content */
    public static function cuisines(): array
    {
        if (! isset(self::$cache['cuisines'])) {
            $all = [];
            foreach (glob(dirname(__DIR__) . '/data/cuisines/*.php') as $file) {
                $all[basename($file, '.php')] = require $file;
            }
            ksort($all);
            self::$cache['cuisines'] = $all;
        }

        return self::$cache['cuisines'];
    }

    /** every concept by name: [cuisine, name, segment, description, signatures, types] */
    public static function concepts(): array
    {
        if (! isset(self::$cache['concepts'])) {
            $all = [];
            foreach (self::cuisines() as $key => $cuisine) {
                foreach ($cuisine['concepts'] as $c) {
                    $all[$c[0]] = [
                        'cuisine' => $key, 'name' => $c[0], 'segment' => $c[1], 'description' => $c[2],
                        'signatures' => $c[3] ?? [],
                        'types' => array_values(array_unique([...$cuisine['types'], ...($cuisine['extra_types'][$c[1]] ?? [])])),
                    ];
                }
            }
            self::$cache['concepts'] = $all;
        }

        return self::$cache['concepts'];
    }

    public static function tier(string $segment): string
    {
        return self::TIERS[$segment] ?? throw new \RuntimeException("Unknown segment {$segment}.");
    }

    // sized Unsplash URL of the n-th photo of a category (rotating, so photos are spread)
    public static function image(string $category, int $n, string $use = 'plate'): ?string
    {
        $pool = self::data('images')[$category] ?? [];
        if ($pool === []) {
            return null;
        }
        $size = ['plate' => 'w=480&h=360', 'restaurant' => 'w=900&h=600', 'type' => 'w=600&h=750'][$use];

        return 'https://images.unsplash.com/photo-' . $pool[$n % count($pool)] . "?auto=format&fit=crop&{$size}&q=70";
    }

    public static function ingredientNames(string $list): array
    {
        return array_values(array_filter(array_map(fn ($n) => mb_strtolower(trim($n)), explode(',', $list))));
    }

    public static function slug(string $text): string
    {
        return Str::slug($text, '-');
    }
}
