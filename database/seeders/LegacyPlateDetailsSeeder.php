<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\Declarations;
use Database\Seeders\Support\PlateWriter;
use Database\Seeders\Support\Registry;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Structured details for the historic plates of PlatesTableSeeder (data/legacy_plates.php):
// ingredients, course and the restaurant declarations. Only plates without ingredients are
// touched, once; their name, price and photo never change. The display label "ingredients"
// becomes the list of the structured ingredients (before it often listed allergens).
class LegacyPlateDetailsSeeder extends Seeder
{
    // cuisine of the historic restaurants, for the cross-contact context of their declarations
    private const CUISINES = [
        "Mc Donald's" => 'fast_food', 'Burger King' => 'fast_food', 'the garage' => 'pizzeria', 'Casa Guadalajara' => 'mexican',
        'Hyack' => 'sushi', 'La gondola' => 'italian_fine', "'O vesuvio" => 'pizzeria', "Peri's" => 'seafood', 'thai food' => 'thai',
        'Il cacciatore' => 'italian_fine', 'La cabrera' => 'argentinian', 'La lanterna' => 'italian_fine', 'Green food' => 'vegan',
        'Il Rancio' => 'italian_fine', 'Starbucks' => 'brunch', 'Esencia' => 'spanish',
    ];

    private const DRINKS = ['water', 'soft_drink', 'beer', 'wine_red', 'wine_white', 'champagne', 'hot_drink'];

    public function run(): void
    {
        Registry::reset();
        $map = Catalog::data('legacy_plates');
        $plates = DB::table('plates')
            ->join('restaurants', 'restaurants.id', '=', 'plates.restaurant_id')
            ->whereIn('restaurants.name', array_keys(self::CUISINES))
            ->whereNotExists(fn ($q) => $q->from('plate_ingredient')->whereColumn('plate_ingredient.plate_id', 'plates.id'))
            ->get(['plates.id', 'plates.name', 'restaurants.name as restaurant']);
        $writer = new PlateWriter();

        DB::transaction(function () use ($plates, $map, $writer) {
            foreach ($plates as $plate) {
                $key = "{$plate->restaurant}|{$plate->name}";
                [$course, $list, $main] = $map[$key] ?? throw new \RuntimeException("LegacyPlateDetailsSeeder: no data for {$key} in data/legacy_plates.php.");
                $names = Catalog::ingredientNames($list);
                $ingredients = Registry::rows($names, "LegacyPlateDetailsSeeder {$key}");
                $cuisine = self::CUISINES[$plate->restaurant];
                $declarations = Declarations::forPlate(
                    Declarations::restaurantPolicy($plate->restaurant, $cuisine), $names, $ingredients,
                    in_array($course, self::DRINKS, true), $cuisine, new Rng("legacy:{$key}"),
                );
                DB::table('plates')->where('id', $plate->id)->update([
                    'course' => $course,
                    'ingredients' => implode(', ', $names),
                    'allergen_info' => $declarations['allergen_info'],
                ]);
                $writer->add($plate->id, $ingredients, $main, $declarations);
            }
            $writer->flush();
        });
    }
}
