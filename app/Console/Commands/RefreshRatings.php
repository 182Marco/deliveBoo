<?php

namespace App\Console\Commands;

use App\Reviews\RestaurantRating;
use Illuminate\Console\Command;

// Safety net: recompute reviews.is_current and the rating columns of every restaurant from the rows
// (for example after editing the reviews table by hand). The app keeps them updated by itself.
class RefreshRatings extends Command
{
    protected $signature = 'foodsy:refresh-ratings';

    protected $description = 'Recompute the rating average, count and ranking score of every restaurant from its reviews';

    public function handle(): int
    {
        RestaurantRating::refreshAll();
        $this->info('Ratings refreshed.');

        return self::SUCCESS;
    }
}
