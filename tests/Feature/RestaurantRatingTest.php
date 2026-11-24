<?php

namespace Tests\Feature;

use App\Restaurant;
use App\Review;
use App\Reviews\RestaurantRating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** The only writer of restaurants.rating_* : arithmetic average, count and Bayesian ranking score. */
class RestaurantRatingTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();
        config(['reviews.prior_weight' => 10, 'reviews.prior_mean' => 3.8]);
        $this->restaurant = Restaurant::where('name', "Mc Donald's")->firstOrFail();
        DB::table('reviews')->where('restaurant_id', $this->restaurant->id)->delete();
    }

    private function votes(array $ratings): void
    {
        DB::table('reviews')->insert(array_map(fn ($r) => ['restaurant_id' => $this->restaurant->id, 'rating' => $r], $ratings));
    }

    public function testAverageCountAndScoreAreDistinct()
    {
        $this->votes([5, 5, 4]);
        RestaurantRating::refresh($this->restaurant->id);

        $fresh = $this->restaurant->fresh();
        $this->assertSame(3, $fresh->rating_count);
        $this->assertSame(4.67, $fresh->rating_avg);
        // (10 × 3.8 + 14) / 13
        $this->assertSame(4.0, $fresh->rating_score);
    }

    public function testNoReviewsMeansNoAverageAndNoScore()
    {
        RestaurantRating::refresh($this->restaurant->id);

        $fresh = $this->restaurant->fresh();
        $this->assertSame(0, $fresh->rating_count);
        $this->assertNull($fresh->rating_avg);
        $this->assertNull($fresh->rating_score);
    }

    public function testRefreshAlwaysRecomputesFromTheRows()
    {
        $this->votes([2, 4]);
        // stale or corrupted aggregates (a write that raced, a manual edit): the next refresh fixes them
        DB::table('restaurants')->where('id', $this->restaurant->id)->update(['rating_count' => 99, 'rating_avg' => 1, 'rating_score' => 1]);
        RestaurantRating::refresh($this->restaurant->id);
        RestaurantRating::refresh($this->restaurant->id);

        $fresh = $this->restaurant->fresh();
        $this->assertSame(2, $fresh->rating_count);
        $this->assertSame(3.0, $fresh->rating_avg);
    }

    public function testWritesAreSerializedAndAtomic()
    {
        $this->votes([4]);
        RestaurantRating::refresh($this->restaurant->id);

        // a change that fails leaves rows and aggregates untouched (one transaction)
        try {
            RestaurantRating::write($this->restaurant->id, function () {
                $this->votes([1, 1, 1]);
                throw new \RuntimeException('boom');
            });
            $this->fail('the exception must reach the caller');
        } catch (\RuntimeException) {
        }
        $this->assertSame(1, Review::where('restaurant_id', $this->restaurant->id)->count());
        $this->assertSame(4.0, $this->restaurant->fresh()->rating_avg);

        // two writes in a row: the second one sees the rows of the first one
        RestaurantRating::write($this->restaurant->id, fn () => $this->votes([2]));
        RestaurantRating::write($this->restaurant->id, fn () => $this->votes([3]));
        $fresh = $this->restaurant->fresh();
        $this->assertSame(3, $fresh->rating_count);
        $this->assertSame(3.0, $fresh->rating_avg);
    }

    public function testRefreshAllMatchesEveryRestaurant()
    {
        $this->votes([5, 1]);
        DB::table('restaurants')->update(['rating_count' => 0, 'rating_avg' => null, 'rating_score' => null]);
        $this->artisan('foodsy:refresh-ratings')->assertSuccessful();

        foreach (Restaurant::all() as $r) {
            $counted = Review::counted()->where('restaurant_id', $r->id);
            $this->assertSame((clone $counted)->count(), $r->rating_count, $r->name);
        }
        $this->assertSame(3.0, $this->restaurant->fresh()->rating_avg);
    }
}
