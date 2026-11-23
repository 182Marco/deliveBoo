<?php

namespace Tests\Feature;

use App\Order;
use App\User;
use Database\Seeders\CustomerProfilesSeeder;
use Database\Seeders\OrderHistorySeeder;
use Database\Seeders\Support\CatalogPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Registered customers and Mario Rossi's history (dev/PIANO_AUTH_CLIENTI.md, step 7.3), on the shared test seed. */
class CustomerProfilesSeederTest extends TestCase
{
    use RefreshDatabase;

    private function mario(): User
    {
        return User::where('email', config('demo.accounts.customer.email'))->firstOrFail();
    }

    public function testMarioHasACoherentHistory()
    {
        $mario = $this->mario();
        $orders = $mario->orders()->with('plates')->get();

        $this->assertGreaterThan(5, $orders->count());
        foreach ($orders as $order) {
            $this->assertSame($mario->email, $order->customer_email);
            $this->assertSame('Rossi', $order->customer_lastName);
            $created = Carbon::parse($order->created_at);
            $this->assertTrue($created->betweenIncluded(OrderHistorySeeder::FROM, Carbon::parse(OrderHistorySeeder::TO)->endOfDay()));
            // lunch at the office on weekdays, dinner at home
            if ($created->hour < 15) {
                $this->assertFalse($created->isWeekend(), "lunch order {$order->id} on a weekend");
                $this->assertSame(CustomerProfilesSeeder::OFFICE['address'], $order->customer_address);
            } else {
                $this->assertSame(CustomerProfilesSeeder::HOME['address'], $order->customer_address);
            }
            // the price paid is the sum of its lines
            $this->assertEqualsWithDelta($order->plates->sum(fn ($p) => $p->pivot->quantity * $p->pivot->unit_price), $order->price, 0.01);
            $this->assertNotContains($order->restaurant->segment, ['fine_dining', 'starred']);
        }
        // the account exists before its first order, and has a phone for the checkout
        $this->assertTrue($mario->created_at->lessThanOrEqualTo($orders->min('created_at')));
        $this->assertNotNull($mario->phone);
    }

    public function testMarioOrdersAgainFromHisPlaces()
    {
        $orders = $this->mario()->orders()->get();

        // fewer restaurants than orders: he has regular places
        $this->assertLessThan($orders->count(), $orders->pluck('restaurant_id')->unique()->count());
    }

    public function testHisHabitIsReadableFromTheDietaryTags()
    {
        $habit = DB::table('dietary_tags')->where('code', CustomerProfilesSeeder::HABIT)->value('id');
        // share of dishes (drinks aside) declared "yes" for the habit; one row per order line or per menu dish
        $share = function ($query) use ($habit) {
            $row = $query->whereNotIn('plates.course', OrderHistorySeeder::DRINK_COURSES)
                ->leftJoin('dietary_tag_plate', fn ($j) => $j->on('dietary_tag_plate.plate_id', '=', 'plates.id')
                    ->where('dietary_tag_plate.dietary_tag_id', $habit)->where('dietary_tag_plate.status', 'yes'))
                ->selectRaw('count(*) AS n, count(dietary_tag_plate.plate_id) AS yes')->first();

            return $row->yes / max(1, $row->n);
        };
        $mario = $this->mario();
        $restaurants = $mario->orders()->distinct()->pluck('restaurant_id');

        $ordered = $share(DB::table('order_plate')->join('orders', 'orders.id', '=', 'order_plate.order_id')
            ->join('plates', 'plates.id', '=', 'order_plate.plate_id')->where('orders.user_id', $mario->id));
        $menus = $share(DB::table('plates')->whereNull('plates.deleted_at')->whereIn('plates.restaurant_id', $restaurants));

        // clearly above a random choice in the same restaurants
        $this->assertGreaterThan($menus + 0.2, $ordered);
    }

    public function testFrequentSyntheticCustomersHaveAnAccountWithTheirOrders()
    {
        $count = CatalogPlan::profile()['registered_customers'];
        $synthetic = array_column(OrderHistorySeeder::customers(CatalogPlan::profile()['customers']), 'email');
        $registered = User::where('role', User::CUSTOMER)->whereIn('email', $synthetic)->get();

        $this->assertCount($count, $registered);
        foreach ($registered as $user) {
            $guestOrders = Order::where('customer_email', $user->email)->whereNull('user_id')->count();
            $this->assertSame(0, $guestOrders, "{$user->email} still has guest orders");
            $first = Order::where('user_id', $user->id)->min('created_at');
            $this->assertNotNull($first);
            $this->assertTrue($user->created_at->lessThanOrEqualTo(Carbon::parse($first)), "{$user->email} registered after the first order");
            $this->assertNull($user->vat_number);
        }
        // the others stay guests
        $this->assertGreaterThan(0, Order::whereIn('customer_email', $synthetic)->whereNull('user_id')->count());
    }

    public function testOnlyCustomersOwnOrders()
    {
        $this->assertSame(0, Order::whereHas('user', fn ($q) => $q->where('role', '!=', User::CUSTOMER))->count());
        // every linked order carries the email of its account
        $this->assertSame(0, DB::table('orders')->join('users', 'users.id', '=', 'orders.user_id')
            ->whereColumn('users.email', '!=', 'orders.customer_email')->count());
    }

    public function testRunningAgainChangesNothing()
    {
        $before = [Order::count(), User::count(), Order::whereNotNull('user_id')->count()];

        $this->seed(CustomerProfilesSeeder::class);

        $this->assertSame($before, [Order::count(), User::count(), Order::whereNotNull('user_id')->count()]);
    }
}
