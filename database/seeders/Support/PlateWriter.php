<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;

// Bulk writer of the plate pivots (ingredients, restaurant allergen declarations, dietary tags).
final class PlateWriter
{
    private array $ingredients = [];
    private array $allergens = [];
    private array $diet = [];
    private array $allergenIds;
    private array $tagIds;

    public function __construct()
    {
        $this->allergenIds = DB::table('allergens')->pluck('id', 'code')->all();
        $this->tagIds = DB::table('dietary_tags')->pluck('id', 'code')->all();
        if (! $this->allergenIds || ! $this->tagIds) {
            throw new \RuntimeException('Plate seeders: run AllergensSeeder and DietaryTagsSeeder first.');
        }
    }

    public function add(int $plateId, array $ingredients, int $main, array $declarations): void
    {
        foreach (array_values($ingredients) as $pos => $row) {
            $this->ingredients[] = ['plate_id' => $plateId, 'ingredient_id' => $row['id'], 'is_main' => $pos < $main, 'position' => $pos];
        }
        foreach ($declarations['allergens'] as $code => $status) {
            $this->allergens[] = ['plate_id' => $plateId, 'allergen_id' => $this->allergenIds[$code], 'status' => $status];
        }
        foreach ($declarations['diet'] as $code => [$status, $source]) {
            $this->diet[] = ['plate_id' => $plateId, 'dietary_tag_id' => $this->tagIds[$code], 'status' => $status, 'source' => $source];
        }
    }

    public function flush(): void
    {
        foreach (['plate_ingredient' => 'ingredients', 'allergen_plate' => 'allergens', 'dietary_tag_plate' => 'diet'] as $table => $prop) {
            foreach (array_chunk($this->{$prop}, 1000) as $chunk) {
                DB::table($table)->insert($chunk);
            }
            $this->{$prop} = [];
        }
    }
}
