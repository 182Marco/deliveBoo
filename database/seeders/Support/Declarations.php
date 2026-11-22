<?php

namespace Database\Seeders\Support;

use App\Allergen;
use App\Dietary\Derivation;
use App\DietaryTag;
use App\Plate;

// What a restaurant declares about its plates. The allergens DERIVED from the ingredients stay a
// query: here are only the restaurant's statements, simulated with a policy per restaurant:
// ~60% declare completely, ~20% partially, ~20% nothing (their plates stay "unknown").
// Cross-contact ("may contain") comes from the kitchen context, never from the ingredients.
final class Declarations
{
    // possible cross-contact by cuisine (shared fryers, flours, nuts on the pastry bench, …)
    private const CROSS_CONTACT = [
        'pizzeria' => ['gluten', 'milk', 'nuts'], 'bakery' => ['sesame', 'nuts', 'eggs', 'milk'], 'pastry' => ['nuts', 'peanuts', 'eggs', 'soybeans'],
        'gelato' => ['nuts', 'peanuts', 'eggs', 'gluten', 'soybeans'], 'fast_food' => ['gluten', 'sesame', 'milk', 'eggs'],
        'american' => ['gluten', 'sesame', 'milk', 'mustard'], 'street_food' => ['gluten', 'milk', 'eggs'],
        'sushi' => ['sesame', 'soybeans', 'crustaceans', 'fish'], 'ramen' => ['sesame', 'soybeans', 'eggs', 'gluten'],
        'chinese' => ['sesame', 'soybeans', 'peanuts', 'crustaceans'], 'korean' => ['sesame', 'soybeans', 'fish'],
        'thai' => ['peanuts', 'crustaceans', 'fish', 'soybeans'], 'vietnamese' => ['peanuts', 'fish', 'crustaceans'],
        'indian' => ['nuts', 'mustard', 'peanuts', 'milk'], 'mexican' => ['gluten', 'milk'], 'seafood' => ['crustaceans', 'molluscs', 'fish', 'gluten'],
        'lebanese' => ['sesame', 'nuts'], 'turkish' => ['sesame', 'nuts', 'gluten'], 'greek' => ['sesame', 'milk', 'gluten'],
        'vegan' => ['nuts', 'peanuts', 'sesame', 'soybeans'], 'vegetarian' => ['nuts', 'eggs', 'milk'], 'poke' => ['sesame', 'soybeans', 'fish', 'crustaceans'],
        'trattoria' => ['gluten', 'eggs', 'milk', 'celery'], 'italian_fine' => ['nuts', 'gluten', 'crustaceans'], 'french' => ['nuts', 'gluten', 'celery'],
        'brunch' => ['gluten', 'nuts', 'eggs'], 'fusion' => ['sesame', 'soybeans', 'crustaceans'],
    ];

    private const SPICY = [
        'chilli pepper', 'chilli flakes', 'chilli oil', 'jalapeño', 'sriracha', 'gochujang', 'gochugaru', 'harissa', 'berbere',
        'nduja', 'spicy salami', 'sichuan pepper', 'chipotle', 'tabasco', 'thai green curry paste', 'thai red curry paste',
        'salsa brava', 'spicy mayo', 'shichimi togarashi',
    ];

    private const HALAL_CUISINES = ['moroccan', 'turkish', 'lebanese'];

    /** @return array{policy: ?string, may: string[]} */
    public static function restaurantPolicy(string $restaurant, string $cuisine): array
    {
        $rng = new Rng("declare:{$restaurant}");
        $x = $rng->float();
        $policy = $x < 0.6 ? Plate::ALLERGENS_COMPLETE : ($x < 0.8 ? Plate::ALLERGENS_PARTIAL : null);
        $context = self::CROSS_CONTACT[$cuisine] ?? [];
        $may = $policy === Plate::ALLERGENS_COMPLETE && $context ? array_slice($rng->shuffle($context), 0, $rng->int(1, min(3, count($context)))) : [];

        return ['policy' => $policy, 'may' => $may];
    }

    /**
     * @param  array  $ingredients  Registry rows of the plate
     * @return array{allergen_info: ?string, allergens: array<string, string>, diet: array<string, array{0: string, 1: string}>}
     */
    public static function forPlate(array $policy, array $names, array $ingredients, bool $drink, string $cuisine, Rng $rng): array
    {
        $derived = array_values(array_unique(array_merge([], ...array_column($ingredients, 'allergens'))));
        $allergens = [];
        $info = $policy['policy'];

        if ($info === Plate::ALLERGENS_COMPLETE) {
            $contains = $derived;
            // a few complete declarations forget an allergen: they must never look safe
            if ($contains && ! $drink && $rng->chance(0.03)) {
                array_splice($contains, $rng->int(0, count($contains) - 1), 1);
            }
            $allergens = array_fill_keys($contains, Allergen::CONTAINS);
            if (! $drink) {
                foreach (array_diff($policy['may'], $derived) as $code) {
                    $allergens[$code] = Allergen::MAY_CONTAIN;
                }
            }
        } elseif ($info === Plate::ALLERGENS_PARTIAL && $derived) {
            $some = array_slice($rng->shuffle($derived), 0, max(1, intdiv(count($derived) + 1, 2)));
            $allergens = array_fill_keys($some, Allergen::CONTAINS);
        }

        $diet = [];
        if ($info !== null) {
            foreach (Derivation::derive($ingredients, $info, $allergens) as $code => $status) {
                $diet[$code] = [$status, DietaryTag::FROM_INGREDIENTS];
            }
            // the restaurant's own statements win over the derived ones
            if (! $drink && array_intersect($names, self::SPICY)) {
                $diet['spicy'] = [DietaryTag::YES, DietaryTag::FROM_RESTAURANT];
            }
            if (in_array($cuisine, self::HALAL_CUISINES, true) && ! $drink
                && (array_filter($names, fn ($n) => str_starts_with($n, 'halal ')) || ($diet['vegetarian'][0] ?? null) === DietaryTag::YES)
                && ! array_filter($ingredients, fn ($i) => $i['category'] === 'alcohol')) {
                $diet['halal'] = [DietaryTag::YES, DietaryTag::FROM_RESTAURANT];
            }
            if ($info === Plate::ALLERGENS_PARTIAL) {
                foreach (['vegan', 'vegetarian'] as $code) {
                    if (($diet[$code][0] ?? null) === DietaryTag::YES && $rng->chance(0.25)) {
                        $diet[$code] = [DietaryTag::UNKNOWN, DietaryTag::FROM_RESTAURANT];
                    }
                }
            }
        }

        return ['allergen_info' => $info, 'allergens' => $allergens, 'diet' => $diet];
    }
}
