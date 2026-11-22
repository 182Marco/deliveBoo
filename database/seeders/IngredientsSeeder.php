<?php

namespace Database\Seeders;

use App\Allergen;
use App\Ingredient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// reference data: the ingredient registry (data/ingredients.php) and the allergens each
// ingredient contains. Idempotent by name: existing ingredients are never overwritten.
class IngredientsSeeder extends Seeder
{
    public function run(): void
    {
        $allergens = Allergen::pluck('id', 'code');
        if ($allergens->isEmpty()) {
            throw new \RuntimeException('IngredientsSeeder: no allergens, run AllergensSeeder first.');
        }

        $rows = collect(require __DIR__ . '/data/ingredients.php')->map(fn (string $line) => self::parse($line));

        DB::transaction(function () use ($rows, $allergens) {
            $existing = Ingredient::pluck('id', 'name');
            $now = now();
            $rows->reject(fn ($r) => isset($existing[$r['name']]))
                ->map(fn ($r) => [...collect($r)->except('allergens')->all(), 'info_status' => Ingredient::VERIFIED, 'created_at' => $now, 'updated_at' => $now])
                ->chunk(200)->each(fn ($chunk) => DB::table('ingredients')->insert($chunk->values()->all()));

            $ids = Ingredient::pluck('id', 'name');
            $linked = DB::table('allergen_ingredient')->get(['ingredient_id', 'allergen_id'])
                ->map(fn ($l) => "{$l->ingredient_id}-{$l->allergen_id}")->flip();
            $pivot = [];
            foreach ($rows as $r) {
                foreach ($r['allergens'] as $code) {
                    if (! isset($allergens[$code])) {
                        throw new \RuntimeException("IngredientsSeeder: unknown allergen {$code} for {$r['name']}.");
                    }
                    if (! isset($linked["{$ids[$r['name']]}-{$allergens[$code]}"])) {
                        $pivot[] = ['ingredient_id' => $ids[$r['name']], 'allergen_id' => $allergens[$code]];
                    }
                }
            }
            collect($pivot)->chunk(500)->each(fn ($chunk) => DB::table('allergen_ingredient')->insert($chunk->values()->all()));
        });
    }

    // "name|category|origin|lactose|nickel|allergens" -> columns
    public static function parse(string $line): array
    {
        [$name, $category, $origin, $lactose, $nickel, $allergens] = explode('|', $line);

        return [
            'name' => Ingredient::normalizeName($name),
            'category' => $category,
            'origin' => $origin,
            'contains_lactose' => $lactose === '' ? null : (bool) $lactose,
            'nickel_level' => $nickel === '' ? null : $nickel,
            'allergens' => $allergens === '' ? [] : explode(',', $allergens),
        ];
    }
}
