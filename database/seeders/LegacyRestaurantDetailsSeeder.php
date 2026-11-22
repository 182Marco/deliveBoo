<?php

namespace Database\Seeders;

use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Segment, description, price range, location, delivery terms and opening hours of the historic
// restaurants (data/legacy_restaurants.php). Only empty columns are filled and hours are added
// only to a restaurant that has none: what a restaurateur changed in the admin stays as it is.
class LegacyRestaurantDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $data = Catalog::data('legacy_restaurants');
        $restaurants = DB::table('restaurants')->whereIn('name', array_keys($data))->get()->keyBy('name');
        if ($restaurants->count() !== count($data)) {
            throw new \RuntimeException('LegacyRestaurantDetailsSeeder: historic restaurants missing, run RestaurantsTableSeeder first.');
        }
        $withHours = DB::table('opening_hours')->whereIn('restaurant_id', $restaurants->pluck('id'))->distinct()->pluck('restaurant_id')->flip();

        DB::transaction(function () use ($data, $restaurants, $withHours) {
            $hours = [];
            foreach ($data as $name => [$cuisine, $segment, $description, $postal, $province, $lat, $lng]) {
                $r = $restaurants[$name];
                $rng = new Rng("legacy-place:{$name}");
                $tier = Catalog::tier($segment);
                $values = [
                    'segment' => $segment,
                    'description' => $description,
                    'price_range' => Catalog::PRICE_RANGE[$segment],
                    'postal_code' => $postal,
                    'province' => $province,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'delivery_fee' => match ($tier) { 'base' => 1.99, 'mid' => 2.49, 'high' => 4.9 },
                    'min_order' => match ($tier) { 'base' => 10, 'mid' => 15, 'high' => 60 },
                    'prep_minutes' => match ($tier) { 'base' => $rng->int(12, 25), 'mid' => $rng->int(25, 40), 'high' => $rng->int(45, 75) },
                ];
                $empty = array_filter($values, fn ($v, $column) => $r->{$column} === null, ARRAY_FILTER_USE_BOTH);
                if ($empty) {
                    DB::table('restaurants')->where('id', $r->id)->update($empty);
                }
                if (! isset($withHours[$r->id])) {
                    array_push($hours, ...CatalogRestaurantsSeeder::hours($r->id, ['name' => $name, 'cuisine' => $cuisine, 'segment' => $segment]));
                }
            }
            collect($hours)->chunk(500)->each(fn ($c) => DB::table('opening_hours')->insert($c->values()->all()));
        });
    }
}
