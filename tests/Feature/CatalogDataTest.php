<?php

namespace Tests\Feature;

use App\Plate;
use Database\Seeders\IngredientsSeeder;
use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\CatalogPlan;
use Database\Seeders\Support\MenuPlan;
use Tests\TestCase;

/**
 * The data files of the generated catalog (database/seeders/data) and the plan built from them,
 * on the FULL profile, without touching the database.
 */
class CatalogDataTest extends TestCase
{
    private const DRINK_COURSES = ['water', 'soft_drink', 'beer', 'wine_white', 'wine_red', 'wine_rose', 'sparkling', 'champagne', 'dessert_wine', 'spirit', 'cocktail', 'hot_drink'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['seeding.profile' => 'full']);
    }

    private function registry(): array
    {
        return collect(Catalog::data('ingredients'))->map(fn ($l) => IngredientsSeeder::parse($l))->keyBy('name')->all();
    }

    public function testEveryRecipeUsesKnownIngredientsCoursesAndPhotos()
    {
        $registry = $this->registry();
        $images = Catalog::data('images');
        $allergens = array_keys(Catalog::data('allergens'));
        $errors = [];
        $check = function (string $where, string $ingredients, string $course, string $img) use (&$errors, $registry, $images) {
            foreach (Catalog::ingredientNames($ingredients) as $name) {
                isset($registry[$name]) || $errors[] = "{$where}: ingredient '{$name}'";
            }
            in_array($course, Plate::COURSES, true) || $errors[] = "{$where}: course {$course}";
            isset($images[$img]) || $errors[] = "{$where}: photo category {$img}";
        };

        foreach ($registry as $name => $row) {
            foreach ($row['allergens'] as $code) {
                in_array($code, $allergens, true) || $errors[] = "ingredient {$name}: allergen {$code}";
            }
            in_array($row['nickel_level'], [null, 'low', 'medium', 'high'], true) || $errors[] = "ingredient {$name}: nickel";
        }
        foreach (Catalog::data('drinks') as $d) {
            $check("drink {$d[0]}", $d[3], $d[1], $d[2]);
            in_array($d[1], self::DRINK_COURSES, true) || $errors[] = "drink {$d[0]}: not a drink course";
        }
        foreach (Catalog::cuisines() as $key => $cuisine) {
            foreach ($cuisine['recipes'] as $r) {
                foreach ($r[3] as $tier => $variants) {
                    in_array($tier, ['base', 'mid', 'high'], true) || $errors[] = "{$key} {$r[0]}: tier {$tier}";
                    foreach (is_array($variants[0]) ? $variants : [$variants] as $v) {
                        $check("{$key} {$r[0]}", $v[1], $r[1], $r[2]);
                        $v[0] > 0 || $errors[] = "{$key} {$r[0]}: price";
                    }
                }
            }
            foreach ($cuisine['concepts'] as $c) {
                array_key_exists($c[1], Catalog::TIERS) || $errors[] = "{$key} {$c[0]}: segment {$c[1]}";
                foreach ($c[3] ?? [] as $s) {
                    $check("{$key} {$c[0]} signature {$s[0]}", $s[4], $s[1], $s[2]);
                }
            }
        }
        foreach (Catalog::data('legacy_plates') as $key => [$course, $list]) {
            $check("legacy {$key}", $list, $course, 'pizza');
        }

        $this->assertSame([], $errors);
    }

    public function testThePlanIsCredible()
    {
        $owners = CatalogPlan::owners();
        $restaurants = CatalogPlan::restaurants();
        $counts = array_map(fn ($o) => count($o['restaurants']), $owners);

        // every owner has between 1 and 40 restaurants, most of them only a few
        $this->assertGreaterThanOrEqual(1, min($counts));
        $this->assertLessThanOrEqual(40, max($counts));
        $this->assertGreaterThan(count($counts) / 2, count(array_filter($counts, fn ($c) => $c <= 2)));
        // names and slugs are unique, names fit the column
        $this->assertSame(count($restaurants), count(array_unique(array_column($restaurants, 'name'))));
        $this->assertSame(count($restaurants), count(array_unique(array_column($restaurants, 'slug'))));
        $this->assertLessThanOrEqual(50, max(array_map('mb_strlen', array_column($restaurants, 'name'))));
        // every concept is used and belongs to one owner only
        $owner = [];
        foreach ($restaurants as $r) {
            $this->assertSame($owner[$r['concept']] ??= $r['owner'], $r['owner'], $r['concept']);
        }
        $this->assertCount(count(Catalog::concepts()), $owner);
        // VAT numbers: 11 digits, unique
        $vats = array_filter(array_column($owners, 'vat_number'));
        $this->assertSame(count($vats), count(array_unique($vats)));
        $this->assertSame([], array_filter($vats, fn ($v) => ! preg_match('/^[1-9]\d{10}$/', $v)));
    }

    public function testMenusMatchThePositioningOfTheRestaurant()
    {
        $premiumOnly = ['champagne', 'tasting_menu'];
        foreach (CatalogPlan::restaurants() as $r) {
            $menu = MenuPlan::build($r);
            $tier = Catalog::tier($r['segment']);
            $this->assertNotEmpty(array_filter($menu, fn ($i) => ! $i['drink']), "{$r['name']} has no food");
            $this->assertSame(count($menu), count(array_unique(array_column($menu, 'name'))), "{$r['name']} repeats a name");
            if ($tier !== 'high') {
                // no champagne or tasting menu in a pizzeria, a fast food or a trattoria
                $this->assertSame([], array_values(array_filter(array_column($menu, 'course'), fn ($c) => in_array($c, $premiumOnly, true))), $r['name']);
            }
            if ($tier === 'base') {
                $this->assertLessThanOrEqual(30, max(array_column(array_filter($menu, fn ($i) => $i['serves'] === 1), 'price')), "{$r['name']} is a budget place");
            }
            foreach ($menu as $item) {
                $this->assertGreaterThan(0, $item['price'], $item['name']);
                $this->assertNotEmpty($item['ingredients'], $item['name']);
            }
        }
    }
}
