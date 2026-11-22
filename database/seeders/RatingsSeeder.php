<?php

namespace Database\Seeders;

use Database\Seeders\Support\CatalogPlan;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Rates of the generated restaurants: every place has a latent quality (its segment plus noise)
// and a number of votes (a handful for the new ones, hundreds for the popular ones). Votes are
// drawn around the quality, so the averages spread from about 2.8 to 4.9.
// Idempotent: a restaurant that already has rates is skipped.
class RatingsSeeder extends Seeder
{
    private const QUALITY = [
        'fast_food' => 3.5, 'budget' => 3.7, 'street_food' => 3.9, 'ethnic' => 3.9, 'trattoria' => 4.0,
        'traditional' => 4.0, 'premium' => 4.3, 'fine_dining' => 4.5, 'starred' => 4.7,
    ];

    public function run(): void
    {
        $scale = CatalogPlan::profile()['rates_scale'];
        $plan = CatalogPlan::restaurants();
        $ids = DB::table('restaurants')->whereIn('name', array_column($plan, 'name'))->pluck('id', 'name');
        $rated = DB::table('rates')->whereIn('restaurant_id', $ids)->distinct()->pluck('restaurant_id')->flip();
        $now = now();
        $rows = [];

        foreach ($plan as $r) {
            $restaurantId = $ids[$r['name']] ?? throw new \RuntimeException('RatingsSeeder: run CatalogRestaurantsSeeder first.');
            if (isset($rated[$restaurantId])) {
                continue;
            }
            $rng = new Rng("rates:{$r['name']}");
            $quality = max(2.3, min(4.95, self::QUALITY[$r['segment']] + $rng->between(-0.9, 0.7)));
            $x = $rng->float();
            // new places have a few votes, most a few dozen, the popular ones hundreds
            $votes = $x < 0.15 ? $rng->int(1, 5) : ($x < 0.85 ? $rng->int(15, 90) : $rng->int(150, 400));
            $votes = max(1, (int) round($votes * $scale));
            for ($v = 0; $v < $votes; $v++) {
                $rows[] = [
                    'restaurant_id' => $restaurantId,
                    'rate' => max(1, min(5, (int) round($quality + $rng->between(-1.6, 1.6)))),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::transaction(fn () => collect($rows)->chunk(1000)->each(fn ($c) => DB::table('rates')->insert($c->values()->all())));
    }
}
