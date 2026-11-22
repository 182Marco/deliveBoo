<?php

namespace Database\Seeders;

use Database\Seeders\Support\CatalogPlan;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Rates of the generated restaurants: every place has a latent quality (its segment plus noise)
// and a number of votes (a handful for the new ones, hundreds for the popular ones). Votes are
// drawn around the quality, so the averages spread from about 2.5 to 4.9.
// Idempotent: a restaurant that already has rates is skipped.
class RatingsSeeder extends Seeder
{
    private const QUALITY = [
        'fast_food' => 3.6, 'budget' => 3.8, 'street_food' => 4.0, 'ethnic' => 4.0, 'trattoria' => 4.1,
        'traditional' => 4.1, 'premium' => 4.4, 'fine_dining' => 4.6, 'starred' => 4.75,
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
            $quality = max(2.3, min(4.95, self::QUALITY[$r['segment']] + $rng->between(-0.9, 0.8)));
            $x = $rng->float();
            // new places have a few votes, most a few dozen, the popular ones hundreds
            $votes = $x < 0.15 ? $rng->int(1, 5) : ($x < 0.85 ? $rng->int(15, 90) : $rng->int(150, 400));
            $votes = max(1, (int) round($votes * $scale));
            for ($v = 0; $v < $votes; $v++) {
                $rows[] = [
                    'restaurant_id' => $restaurantId,
                    'rate' => max(1, min(5, (int) round($quality + $rng->between(-1.1, 1.1)))),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::transaction(fn () => collect($rows)->chunk(1000)->each(fn ($c) => DB::table('rates')->insert($c->values()->all())));
    }
}
