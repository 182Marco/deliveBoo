<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Runs on the in-memory sqlite database configured in phpunit.xml,
 * never on the development database.
 */
class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    private $tables = [
        'users', 'types', 'restaurants', 'plates', 'orders',
        'order_plate', 'restaurant_type', 'rates',
    ];

    private function counts()
    {
        $counts = [];
        foreach ($this->tables as $table) {
            $counts[$table] = DB::table($table)->count();
        }
        return $counts;
    }

    public function testSeedingTwiceDoesNotDuplicateAnything()
    {
        $this->seed();
        $first = $this->counts();

        foreach ($first as $table => $count) {
            $this->assertGreaterThan(0, $count, "$table is empty after seeding");
        }

        $this->seed();
        $this->assertSame($first, $this->counts());
    }

    public function testEverySeederCanBeRunAloneTwice()
    {
        $this->seed();
        $before = $this->counts();

        foreach (['UsersTableSeeder', 'TypesTableSeeder', 'RestaurantsTableSeeder',
                  'PlatesTableSeeder', 'OrdersTableSeeder', 'RatesTableSeeder'] as $seeder) {
            $this->seed($seeder);
        }
        $this->assertSame($before, $this->counts());
    }

    public function testSeederFailsClearlyWhenDependenciesAreMissing()
    {
        // plates without restaurants: clear error and nothing written (transaction)
        try {
            $this->seed('PlatesTableSeeder');
            $this->fail('PlatesTableSeeder should fail without restaurants');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('run RestaurantsTableSeeder first', $e->getMessage());
        }
        $this->assertSame(0, DB::table('plates')->count());
    }

    public function testSeededDataIsConsistent()
    {
        $this->seed();

        // no orphan foreign keys
        $this->assertSame(0, DB::table('restaurants')->leftJoin('users', 'users.id', '=', 'restaurants.user_id')->whereNull('users.id')->count());
        $this->assertSame(0, DB::table('plates')->leftJoin('restaurants', 'restaurants.id', '=', 'plates.restaurant_id')->whereNull('restaurants.id')->count());
        $this->assertSame(0, DB::table('orders')->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')->whereNull('restaurants.id')->count());
        $this->assertSame(0, DB::table('rates')->leftJoin('restaurants', 'restaurants.id', '=', 'rates.restaurant_id')->whereNull('restaurants.id')->count());

        // every plate of an order belongs to the restaurant of the order
        $this->assertSame(0, DB::table('order_plate')
            ->join('orders', 'orders.id', '=', 'order_plate.order_id')
            ->join('plates', 'plates.id', '=', 'order_plate.plate_id')
            ->whereColumn('orders.restaurant_id', '!=', 'plates.restaurant_id')
            ->count());

        // no duplicated pivot rows
        $this->assertSame(0, DB::table('order_plate')->select('order_id', 'plate_id')->groupBy('order_id', 'plate_id')->havingRaw('count(*) > 1')->get()->count());
        $this->assertSame(0, DB::table('restaurant_type')->select('restaurant_id', 'type_id')->groupBy('restaurant_id', 'type_id')->havingRaw('count(*) > 1')->get()->count());

        // every order has plates and its price is the sum of them
        foreach (DB::table('orders')->get() as $order) {
            $sum = DB::table('order_plate')
                ->join('plates', 'plates.id', '=', 'order_plate.plate_id')
                ->where('order_plate.order_id', $order->id)
                ->sum('plates.price');
            $this->assertGreaterThan(0, $sum, "order {$order->id} has no plates");
            $this->assertEqualsWithDelta($sum, $order->price, 0.01, "order {$order->id} price");
        }

        // slugs and names are filled in and plate slugs are unique
        $this->assertSame(0, DB::table('plates')->where('name', '')->orWhere('slug', '')->count());
        $this->assertSame(0, DB::table('restaurants')->where('slug', '')->count());
        $this->assertSame(DB::table('plates')->count(), DB::table('plates')->distinct()->count('slug'));
        $this->assertSame(DB::table('restaurants')->count(), DB::table('restaurants')->distinct()->count('slug'));
        $this->assertSame(DB::table('users')->count(), DB::table('users')->distinct()->count('email'));

        // every restaurant has at least one plate, one type and one rate
        foreach (DB::table('restaurants')->get() as $restaurant) {
            $this->assertGreaterThan(0, DB::table('plates')->where('restaurant_id', $restaurant->id)->count(), "{$restaurant->name} has no plates");
            $this->assertGreaterThan(0, DB::table('restaurant_type')->where('restaurant_id', $restaurant->id)->count(), "{$restaurant->name} has no types");
            $this->assertGreaterThan(0, DB::table('rates')->where('restaurant_id', $restaurant->id)->count(), "{$restaurant->name} has no rates");
        }
    }
}
