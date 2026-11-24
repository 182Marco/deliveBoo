<?php

namespace Tests\Feature\Admin;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Partner statistics (/admin/stats, dev/PIANO_STATISTICHE.md) on a small dataset with known numbers in
 * January 2030, far from the seed. "Today" is 15 February 2030.
 *
 *   owner A: R1 completed 10 (alice), 30 (bob), cancelled 50 (bob), failed 99 (carol), drinks excluded from dishes
 *            R2 completed 20 (alice), in progress 15 (dave)
 *            December 2029: R1 completed 40 (alice) → previous period
 *   owner B: R3 completed 70 (alice)
 *   owner C: no restaurants
 *   reviews (by order): R1 10 → 4★, R1 30 → 2★, R2 20 → 5★, R3 70 → 1★
 */
class StatsTest extends TestCase
{
    use RefreshDatabase;

    private int $ownerA;
    private int $ownerB;
    private int $ownerC;
    private int $r1;
    private int $r2;
    private int $r3;
    private const JANUARY = ['from' => '2030-01-01', 'to' => '2030-01-31'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2030-02-15 10:00:00');
        Cache::flush();

        $this->ownerA = $this->owner('a');
        $this->ownerB = $this->owner('b');
        $this->ownerC = $this->owner('c');
        $this->r1 = $this->restaurant('Stats One', $this->ownerA);
        $this->r2 = $this->restaurant('Stats Two', $this->ownerA);
        $this->r3 = $this->restaurant('Stats Three', $this->ownerB);

        $pizza = $this->plate($this->r1, 'Stats Pizza', 'pizza');
        $cola = $this->plate($this->r1, 'Stats Cola', 'soft_drink');
        $pasta = $this->plate($this->r2, 'Stats Pasta', 'pasta');
        $soup = $this->plate($this->r3, 'Stats Soup', 'soup');

        $o1 = $this->order($this->r1, 10, 'alice', 'paid', 'delivered', '2030-01-07 12:30:00', 30, [[$pizza, 2, 4], [$cola, 1, 2]]);
        $o2 = $this->order($this->r1, 30, 'bob', 'paid', 'delivered', '2030-01-08 20:00:00', 50, [[$pizza, 3, 10]]);
        $this->order($this->r1, 50, 'bob', 'paid', 'cancelled', '2030-01-09 20:00:00', null, [[$pizza, 5, 10]]);
        $this->order($this->r1, 99, 'carol', 'failed', null, '2030-01-10 20:00:00', null, [[$pizza, 1, 99]]);
        $o5 = $this->order($this->r2, 20, 'alice', 'paid', 'delivered', '2030-01-20 19:00:00', 40, [[$pasta, 2, 10]]);
        $this->order($this->r2, 15, 'dave', 'paid', 'received', '2030-01-31 21:00:00', null, [[$pasta, 1, 15]]);
        $this->order($this->r1, 40, 'alice', 'paid', 'delivered', '2029-12-15 12:00:00', 20, [[$pizza, 4, 10]]);
        $o8 = $this->order($this->r3, 70, 'alice', 'paid', 'delivered', '2030-01-15 19:00:00', 25, [[$soup, 7, 10]]);

        foreach ([[$this->r1, $o1, 4], [$this->r1, $o2, 2], [$this->r2, $o5, 5], [$this->r3, $o8, 1]] as [$restaurant, $order, $stars]) {
            DB::table('reviews')->insert(['restaurant_id' => $restaurant, 'order_id' => $order, 'rating' => $stars, 'created_at' => '2030-01-25 10:00:00']);
        }
    }

    // ---------------------------------------------------------------- data

    private function owner(string $key): int
    {
        return DB::table('users')->insertGetId([
            'name' => "Owner {$key}", 'last_name' => 'Stats', 'email' => "owner-{$key}@stats.test", 'password' => bcrypt('secret123'),
            'role' => User::RESTAURATEUR, 'vat_number' => '1234567890'.ord($key), 'email_verified_at' => now(),
        ]);
    }

    private function restaurant(string $name, int $owner): int
    {
        return DB::table('restaurants')->insertGetId(['user_id' => $owner, 'name' => $name, 'slug' => (string) str($name)->slug(), 'phone' => '1', 'city' => 'Statsville', 'address' => 'a']);
    }

    private function plate(int $restaurant, string $name, string $course): int
    {
        return DB::table('plates')->insertGetId(['restaurant_id' => $restaurant, 'name' => $name, 'slug' => (string) str($name)->slug(), 'course' => $course, 'ingredients' => 'test', 'description' => 'test', 'price' => 10, 'visible' => true, 'availability' => 'available']);
    }

    private function order(int $restaurant, float $price, string $who, string $status, ?string $fulfillment, string $at, ?int $minutes, array $lines): int
    {
        $id = DB::table('orders')->insertGetId([
            'restaurant_id' => $restaurant, 'price' => $price, 'status' => $status, 'fulfillment_status' => $fulfillment,
            'customer_name' => ucfirst($who), 'customer_lastName' => 'Secretson', 'customer_email' => "{$who}@stats.test",
            'customer_phone' => '0791112233', 'customer_address' => 'Hiddenstrasse 9',
            'created_at' => $at, 'updated_at' => $at,
            'delivered_at' => $minutes ? date('Y-m-d H:i:s', strtotime($at) + 60 * $minutes) : null,
        ]);
        foreach ($lines as [$plate, $qty, $unit]) {
            DB::table('order_plate')->insert(['order_id' => $id, 'plate_id' => $plate, 'quantity' => $qty, 'unit_price' => $unit]);
        }

        return $id;
    }

    private function as(int $user): static
    {
        return $this->actingAs(User::findOrFail($user));
    }

    private function group(int $user, array $query = self::JANUARY)
    {
        return $this->as($user)->get(route('admin.stats.index', $query))->assertOk()->assertViewIs('admin.stats.show');
    }

    private function single(int $user, int $restaurant, array $query = self::JANUARY)
    {
        return $this->as($user)->get(route('admin.stats.show', ['stat' => $restaurant, ...$query]));
    }

    // ---------------------------------------------------------------- tests

    public function testTheGroupAddsUpItsRestaurants()
    {
        $page = $this->group($this->ownerA);
        $now = $page->viewData('summary')['current'];

        // R1 completed 10 + 30, R2 completed 20 and in progress 15
        $this->assertSame(3, $now['completed_orders']);
        $this->assertSame(60.0, $now['completed_value']);
        $this->assertSame(75.0, $now['gross_value']);
        $this->assertSame(1, $now['cancelled_orders']);
        $this->assertSame(1, $now['failed_orders']);

        // the additive metrics of the group are the sum of its restaurants
        $one = $this->single($this->ownerA, $this->r1)->viewData('summary')['current'];
        $two = $this->single($this->ownerA, $this->r2)->viewData('summary')['current'];
        foreach (['completed_orders', 'completed_value', 'gross_orders', 'gross_value', 'cancelled_orders', 'failed_orders', 'in_progress_value'] as $key) {
            $this->assertEquals($one[$key] + $two[$key], $now[$key], $key);
        }
        $table = collect($page->viewData('table'));
        $this->assertSame([$this->r1, $this->r2], $table->pluck('id')->sort()->values()->all());
        $this->assertEquals($now['gross_value'], $table->sum('gross_value'));
        $this->assertEquals($now['completed_orders'], $table->sum('completed_orders'));

        // distinct customers are never summed: alice ordered at both restaurants
        $this->assertSame(2, $one['customers']);   // alice, bob
        $this->assertSame(2, $two['customers']);   // alice, dave
        $this->assertSame(3, $now['customers']);   // alice, bob, dave

        // stars: weighted on the reviews of the group (4, 2, 5), never an average of averages
        $this->assertSame(['average' => round(11 / 3, 2), 'count' => 3], $page->viewData('rating'));
    }

    public function testTheSingleRestaurantPage()
    {
        $page = $this->single($this->ownerA, $this->r1)->assertOk()->assertViewIs('admin.stats.show');

        $this->assertSame($this->r1, $page->viewData('restaurant')->id);
        $this->assertSame([], $page->viewData('table'));
        $this->assertSame(['average' => 3.0, 'count' => 2], $page->viewData('rating'));
        // drinks are not dishes; cancelled and failed orders do not sell
        $this->assertSame([['name' => 'Stats Pizza', 'quantity' => 5, 'value' => 38.0]], $page->viewData('dishes'));
        // delivery minutes 30 and 50: lower median
        $this->assertSame(30, $page->viewData('summary')['current']['delivery_median_minutes']);
        // compared with December (40 delivered by alice)
        $this->assertSame(40.0, $page->viewData('summary')['previous']['completed_value']);
        // alice had ordered here in December already (returning), bob orders for the first time (new)
        $days = collect($page->viewData('customers')['points'])->keyBy('bucket');
        $this->assertSame(['bucket' => '2030-01-07', 'customers' => 1, 'new' => 0, 'returning' => 1], $days['2030-01-07']);
        $this->assertSame(['bucket' => '2030-01-08', 'customers' => 1, 'new' => 1, 'returning' => 0], $days['2030-01-08']);
    }

    public function testOwnershipIsCheckedEverywhere()
    {
        // someone else's restaurant: the usual "not yours" page, no numbers
        $this->single($this->ownerB, $this->r1)->assertOk()->assertViewIs('admin.notYours.orders');

        // the group of B is only R3
        $page = $this->group($this->ownerB);
        $this->assertSame([$this->r3], collect($page->viewData('table'))->pluck('id')->all());
        $this->assertSame(70.0, $page->viewData('summary')['current']['gross_value']);
        $this->assertSame(['average' => 1.0, 'count' => 1], $page->viewData('rating'));
        $page->assertDontSee('Stats One')->assertDontSee('Stats Two');

        // the scope selector cannot open a foreign restaurant either
        $this->as($this->ownerB)->get(route('admin.stats.index', ['restaurant' => $this->r1, ...self::JANUARY]))
            ->assertRedirect(route('admin.stats.show', ['stat' => $this->r1, ...self::JANUARY]));
        $this->single($this->ownerB, $this->r1)->assertViewIs('admin.notYours.orders');

        // guests log in first; customers and admins have no partner area
        auth()->logout();
        $this->get(route('admin.stats.index'))->assertRedirect('/login');
    }

    public function testTheCacheIsSeparatedByOwnerRestaurantAndPeriod()
    {
        $a = $this->group($this->ownerA)->viewData('summary')['current']['gross_value'];
        $b = $this->group($this->ownerB)->viewData('summary')['current']['gross_value'];
        $one = $this->single($this->ownerA, $this->r1)->viewData('summary')['current']['gross_value'];
        $december = $this->group($this->ownerA, ['from' => '2029-12-01', 'to' => '2029-12-31'])->viewData('summary')['current']['gross_value'];

        $this->assertSame([75.0, 70.0, 40.0, 40.0], [$a, $b, $one, $december]);
    }

    public function testNoPersonalDataOfTheCustomersReachesThePage()
    {
        foreach ([$this->group($this->ownerA), $this->single($this->ownerA, $this->r1)] as $page) {
            $page->assertDontSee('@stats.test')->assertDontSee('0791112233')->assertDontSee('Hiddenstrasse')->assertDontSee('Secretson');
        }
        // the best customers are a first name and an initial
        $fans = $this->group($this->ownerA)->viewData('fans');
        // alice 10 + 20 at two restaurants, bob 30 (his cancelled order does not count), dave 15
        $this->assertSame(['name' => 'Alice S.', 'orders' => 2, 'spent' => 30.0], $fans[0]);
        $this->assertSame(['Alice S.', 'Bob S.', 'Dave S.'], array_column($fans, 'name'));
    }

    public function testOwnersWithoutRestaurantsOrOrders()
    {
        $this->group($this->ownerC)->assertViewHas('empty', true)->assertSee(__('partner.stats.noRestaurants'));

        // a period without orders: the page says so instead of drawing empty charts
        $this->group($this->ownerA, ['from' => '2030-02-01', 'to' => '2030-02-10'])->assertSee(__('platform.charts.empty'));
    }

    public function testThePresetAllStartsFromTheFirstOrderOfTheScope()
    {
        $this->group($this->ownerB)->assertSee('from=2030-01-15', false);
        $this->group($this->ownerA)->assertSee('from=2029-12-15', false);
    }
}
