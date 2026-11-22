<?php

namespace Database\Seeders;

use App\Restaurant;
use App\Type;
use App\User;
use Database\Seeders\Support\CatalogPlan;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// the generated restaurants with their cuisines and opening hours. Idempotent by name:
// existing restaurants are left as they are, missing hours and types are completed.
class CatalogRestaurantsSeeder extends Seeder
{
    public function run(): void
    {
        $plan = CatalogPlan::restaurants();
        $owners = User::whereIn('email', array_unique(array_column($plan, 'owner')))->pluck('id', 'email');
        $types = Type::pluck('id', 'name');
        $existing = Restaurant::whereIn('name', array_column($plan, 'name'))->pluck('id', 'name');
        $now = now();

        DB::transaction(function () use ($plan, $owners, $types, $existing, $now) {
            $rows = [];
            foreach ($plan as $r) {
                $owner = $owners[$r['owner']] ?? throw new \RuntimeException("CatalogRestaurantsSeeder: owner {$r['owner']} not found, run OwnersSeeder first.");
                if (isset($existing[$r['name']])) {
                    continue;
                }
                $rows[] = [
                    'user_id' => $owner, 'created_at' => $now, 'updated_at' => $now,
                    ...array_intersect_key($r, array_flip([
                        'name', 'slug', 'description', 'segment', 'price_range', 'phone', 'email', 'city', 'address', 'postal_code',
                        'province', 'latitude', 'longitude', 'delivery_fee', 'min_order', 'prep_minutes', 'accepting_orders', 'img',
                    ])),
                ];
            }
            collect($rows)->chunk(200)->each(fn ($c) => DB::table('restaurants')->insert($c->values()->all()));

            $ids = Restaurant::whereIn('name', array_column($plan, 'name'))->pluck('id', 'name');
            $linked = DB::table('restaurant_type')->whereIn('restaurant_id', $ids)->get()->map(fn ($l) => "{$l->restaurant_id}-{$l->type_id}")->flip();
            $withHours = DB::table('opening_hours')->whereIn('restaurant_id', $ids)->distinct()->pluck('restaurant_id')->flip();
            $pivot = $hours = [];
            foreach ($plan as $r) {
                $id = $ids[$r['name']];
                foreach ($r['types'] as $type) {
                    $typeId = $types[$type] ?? throw new \RuntimeException("CatalogRestaurantsSeeder: type {$type} not found, run TypesTableSeeder first.");
                    if (! isset($linked["{$id}-{$typeId}"])) {
                        $pivot[] = ['restaurant_id' => $id, 'type_id' => $typeId];
                    }
                }
                if (! isset($withHours[$id])) {
                    array_push($hours, ...self::hours($id, $r));
                }
            }
            collect($pivot)->chunk(500)->each(fn ($c) => DB::table('restaurant_type')->insert($c->values()->all()));
            collect($hours)->chunk(500)->each(fn ($c) => DB::table('opening_hours')->insert($c->values()->all()));
        });
    }

    // weekly services by kind of place: [weekday 0 = Monday, opens, closes]
    public static function hours(int $restaurantId, array $r): array
    {
        $rng = new Rng("hours:{$r['name']}");
        $closed = $rng->int(0, 6);
        $services = match (true) {
            in_array($r['cuisine'], ['bakery', 'pastry', 'brunch'], true) => [['07:00', '19:30']],
            $r['cuisine'] === 'gelato' => [['12:00', '00:00']],
            in_array($r['segment'], ['fast_food', 'budget', 'street_food'], true) => [['11:00', $rng->pick(['23:00', '23:30', '01:00'])]],
            in_array($r['segment'], ['fine_dining', 'starred'], true) => [['19:30', '23:00']],
            default => [['12:00', '14:30'], ['19:00', $rng->pick(['22:30', '23:00', '23:30'])]],
        };
        $rows = [];
        for ($day = 0; $day < 7; $day++) {
            // casual places open every day, the others rest one day (fine dining two)
            if (! in_array($r['segment'], ['fast_food', 'budget', 'street_food'], true) && ($day === $closed || (in_array($r['segment'], ['fine_dining', 'starred'], true) && $day === ($closed + 1) % 7))) {
                continue;
            }
            $dayServices = $services;
            // fine dining also serves Sunday lunch
            if (in_array($r['segment'], ['fine_dining', 'starred'], true) && $day === 6) {
                $dayServices[] = ['12:30', '14:30'];
            }
            foreach ($dayServices as [$open, $close]) {
                $rows[] = ['restaurant_id' => $restaurantId, 'weekday' => $day, 'opens_at' => $open, 'closes_at' => $close];
            }
        }

        return $rows;
    }
}
