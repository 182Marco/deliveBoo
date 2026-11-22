<?php

namespace App\Dietary;

use App\Allergen;
use App\DietaryTag;
use App\Ingredient;
use App\Plate;
use Illuminate\Support\Facades\DB;

/**
 * Dietary tags DERIVED from the ingredient registry (source = "ingredients").
 * Shared by the admin (after an edit) and the seeders (in bulk), so the rules live once.
 *
 * Nothing is derived unless EVERY ingredient is verified: an unknown ingredient makes the
 * plate "not determinable" (no row), never compatible. Rules:
 * - vegan / vegetarian / pescatarian: from the origin of the ingredients;
 * - fruitarian: only fruit, nuts and seeds;
 * - gluten_free / lactose_free: "no" when an ingredient contains it; "yes" only when the
 *   restaurant also declared its allergens as complete without that cross-contact;
 * - low_nickel: indicative, "yes" only if every level is low, "no" if one is high;
 * - halal, spicy: never derived (only the restaurant knows).
 */
class Derivation
{
    public const DERIVABLE = ['vegan', 'vegetarian', 'pescatarian', 'fruitarian', 'gluten_free', 'lactose_free', 'low_nickel'];

    /**
     * @param  array<int, array{origin: ?string, category: ?string, contains_lactose: ?bool, nickel_level: ?string, info_status: string, allergens: string[]}>  $ingredients
     * @param  ?string  $allergenInfo  restaurant declaration: complete | partial | null
     * @param  array<string, string>  $declared  restaurant declaration: allergen code => contains | may_contain
     * @return array<string, string> tag code => yes | no
     */
    public static function derive(array $ingredients, ?string $allergenInfo, array $declared): array
    {
        if ($ingredients === [] || collect($ingredients)->contains(fn ($i) => $i['info_status'] !== Ingredient::VERIFIED)) {
            return [];
        }

        $origins = array_column($ingredients, 'origin');
        $tags = [];
        if (! in_array(null, $origins, true)) {
            $tags['vegan'] = array_intersect($origins, Ingredient::ANIMAL_PRODUCTS) ? DietaryTag::NO : DietaryTag::YES;
            $tags['vegetarian'] = array_intersect($origins, Ingredient::ANIMAL_FLESH) ? DietaryTag::NO : DietaryTag::YES;
            $tags['pescatarian'] = in_array('meat', $origins, true) ? DietaryTag::NO : DietaryTag::YES;
        }

        $categories = array_column($ingredients, 'category');
        if (! in_array(null, $categories, true)) {
            $tags['fruitarian'] = array_diff($categories, ['fruit', 'nut', 'seed']) ? DietaryTag::NO : DietaryTag::YES;
        }

        $derivedAllergens = array_unique(array_merge(...array_column($ingredients, 'allergens')));
        $complete = $allergenInfo === Plate::ALLERGENS_COMPLETE;

        if (in_array('gluten', $derivedAllergens, true) || ($declared['gluten'] ?? null) === Allergen::CONTAINS) {
            $tags['gluten_free'] = DietaryTag::NO;
        } elseif ($complete && ! isset($declared['gluten'])) {
            $tags['gluten_free'] = DietaryTag::YES;
        }

        $lactose = array_column($ingredients, 'contains_lactose');
        if (in_array(true, $lactose, true)) {
            $tags['lactose_free'] = DietaryTag::NO;
        } elseif (! in_array(null, $lactose, true) && $complete && ($declared['milk'] ?? null) !== Allergen::MAY_CONTAIN) {
            $tags['lactose_free'] = DietaryTag::YES;
        }

        $nickel = array_column($ingredients, 'nickel_level');
        if (in_array('high', $nickel, true)) {
            $tags['low_nickel'] = DietaryTag::NO;
        } elseif (array_unique($nickel) === ['low']) {
            $tags['low_nickel'] = DietaryTag::YES;
        }

        return $tags;
    }

    // recomputes the derived rows of one plate; the restaurant's own declarations always win
    public static function refresh(Plate $plate): void
    {
        $plate->load('ingredientList.allergens', 'allergens', 'dietaryTags');
        $ingredients = $plate->ingredientList->map(fn (Ingredient $i) => [
            'origin' => $i->origin,
            'category' => $i->category,
            'contains_lactose' => $i->contains_lactose,
            'nickel_level' => $i->nickel_level,
            'info_status' => $i->info_status,
            'allergens' => $i->allergens->pluck('code')->all(),
        ])->all();
        $declared = $plate->allergens->mapWithKeys(fn ($a) => [$a->code => $a->pivot->status])->all();
        $derived = self::derive($ingredients, $plate->allergen_info, $declared);

        $byRestaurant = $plate->dietaryTags->where('pivot.source', DietaryTag::FROM_RESTAURANT)->pluck('code')->all();
        $tagIds = DietaryTag::whereIn('code', array_keys($derived))->pluck('id', 'code');

        DB::transaction(function () use ($plate, $derived, $byRestaurant, $tagIds) {
            DB::table('dietary_tag_plate')->where('plate_id', $plate->id)->where('source', DietaryTag::FROM_INGREDIENTS)->delete();
            $rows = collect($derived)->except($byRestaurant)->filter(fn ($s, $code) => isset($tagIds[$code]))
                ->map(fn ($status, $code) => [
                    'plate_id' => $plate->id, 'dietary_tag_id' => $tagIds[$code],
                    'status' => $status, 'source' => DietaryTag::FROM_INGREDIENTS,
                ])->values()->all();
            DB::table('dietary_tag_plate')->insert($rows);
        });
    }
}
