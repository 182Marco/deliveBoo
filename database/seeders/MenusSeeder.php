<?php

namespace Database\Seeders;

use App\Plate;
use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\CatalogPlan;
use Database\Seeders\Support\Declarations;
use Database\Seeders\Support\MenuPlan;
use Database\Seeders\Support\PlateWriter;
use Database\Seeders\Support\Registry;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// the menus of the generated restaurants (Support\MenuPlan): plates, structured ingredients and
// the restaurant declarations. Idempotent: a restaurant that already has plates is skipped.
class MenusSeeder extends Seeder
{
    public function run(): void
    {
        Registry::reset();
        $plan = CatalogPlan::restaurants();
        $ids = DB::table('restaurants')->whereIn('name', array_column($plan, 'name'))->pluck('id', 'name');
        if ($ids->count() !== count($plan)) {
            throw new \RuntimeException('MenusSeeder: restaurants missing, run CatalogRestaurantsSeeder first.');
        }
        $withPlates = DB::table('plates')->whereIn('restaurant_id', $ids)->distinct()->pluck('restaurant_id')->flip();
        $slugs = DB::table('plates')->pluck('slug')->flip()->all();
        $photo = [];
        $writer = new PlateWriter();
        $now = now();

        foreach (array_chunk($plan, 20) as $batch) {
            DB::transaction(function () use ($batch, $ids, $withPlates, &$slugs, &$photo, $writer, $now) {
                $pending = [];
                foreach ($batch as $r) {
                    $restaurantId = $ids[$r['name']];
                    if (isset($withPlates[$restaurantId])) {
                        continue;
                    }
                    $rng = new Rng("plates:{$r['name']}");
                    $policy = Declarations::restaurantPolicy($r['name'], $r['cuisine']);
                    $offset = crc32($r['name']) % 97;
                    foreach (MenuPlan::build($r) as $item) {
                        $ingredients = Registry::rows($item['ingredients'], "MenusSeeder {$r['name']} / {$item['name']}");
                        $declarations = Declarations::forPlate($policy, $item['ingredients'], $ingredients, $item['drink'], $r['cuisine'], $rng);
                        $slug = $this->slug($item['name'], $r['slug'], $slugs);
                        $x = $rng->float();
                        $availability = $x < 0.92 ? Plate::AVAILABLE : ($x < 0.97 ? Plate::SOLD_OUT : Plate::UNAVAILABLE);
                        $photo[$item['img']] = ($photo[$item['img']] ?? $offset) + 1;
                        $pending[$slug] = [[
                            'restaurant_id' => $restaurantId,
                            'name' => $item['name'],
                            'slug' => $slug,
                            'ingredients' => implode(', ', $item['ingredients']),
                            'description' => $item['description'],
                            'course' => $item['course'],
                            'visible' => $availability === Plate::AVAILABLE,
                            'availability' => $availability,
                            'available_from_month' => $item['from'],
                            'available_to_month' => $item['to'],
                            'price' => $item['price'],
                            'serves' => $item['serves'],
                            'calories' => $item['calories'],
                            'allergen_info' => $declarations['allergen_info'],
                            'img' => Catalog::image($item['img'], $photo[$item['img']]),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ], $ingredients, $item['main'], $declarations];
                    }
                }
                foreach (array_chunk(array_column($pending, 0), 500) as $chunk) {
                    DB::table('plates')->insert($chunk);
                }
                $plateIds = DB::table('plates')->whereIn('slug', array_keys($pending))->pluck('id', 'slug');
                foreach ($pending as $slug => [, $ingredients, $main, $declarations]) {
                    $writer->add($plateIds[$slug], $ingredients, $main, $declarations);
                }
                $writer->flush();
            });
        }
    }

    // unique slug: the plate name, then the restaurant slug, then a counter
    private function slug(string $name, string $restaurantSlug, array &$taken): string
    {
        $base = Catalog::slug($name) ?: 'plate';
        foreach ([$base, "{$base}-{$restaurantSlug}"] as $slug) {
            if (! isset($taken[$slug])) {
                return $this->take($slug, $taken);
            }
        }
        for ($i = 2; isset($taken["{$base}-{$restaurantSlug}-{$i}"]); $i++);

        return $this->take("{$base}-{$restaurantSlug}-{$i}", $taken);
    }

    private function take(string $slug, array &$taken): string
    {
        $taken[$slug] = true;

        return $slug;
    }
}
