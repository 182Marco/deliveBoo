<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;

// In-memory view of the ingredient registry (name => reference data and allergen codes),
// so thousands of plates can be planned without a query per ingredient.
final class Registry
{
    private static ?array $rows = null;

    public static function reset(): void
    {
        self::$rows = null;
    }

    public static function all(): array
    {
        if (self::$rows === null) {
            $allergens = DB::table('allergen_ingredient')
                ->join('allergens', 'allergens.id', '=', 'allergen_ingredient.allergen_id')
                ->get(['allergen_ingredient.ingredient_id', 'allergens.code'])
                ->groupBy('ingredient_id')->map(fn ($g) => $g->pluck('code')->all());
            self::$rows = DB::table('ingredients')->get()->mapWithKeys(fn ($i) => [$i->name => [
                'id' => $i->id,
                'origin' => $i->origin,
                'category' => $i->category,
                'contains_lactose' => $i->contains_lactose === null ? null : (bool) $i->contains_lactose,
                'nickel_level' => $i->nickel_level,
                'info_status' => $i->info_status,
                'allergens' => $allergens[$i->id] ?? [],
            ]])->all();
        }

        return self::$rows;
    }

    /** @param string[] $names */
    public static function rows(array $names, string $where): array
    {
        $all = self::all();

        return array_map(fn ($n) => $all[$n] ?? throw new \RuntimeException("{$where}: ingredient '{$n}' is not in the registry, run IngredientsSeeder first."), $names);
    }
}
