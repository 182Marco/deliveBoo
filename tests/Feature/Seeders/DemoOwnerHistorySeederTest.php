<?php

namespace Tests\Feature\Seeders;

use App\Order;
use App\User;
use Database\Seeders\DemoOwnerHistorySeeder;
use Database\Seeders\Support\CatalogPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** The history of the demo restaurateur's group, on the shared test seed (profile "test"). */
class DemoOwnerHistorySeederTest extends TestCase
{
    use RefreshDatabase;

    private function restaurantIds(): array
    {
        return DB::table('restaurants as r')->join('users as u', 'u.id', '=', 'r.user_id')
            ->where('u.email', CatalogPlan::ROMITI)->pluck('r.id')->all();
    }

    private function history()
    {
        return Order::whereIn('restaurant_id', $this->restaurantIds())->where(fn ($q) => $q
            ->where('transaction_id', 'like', DemoOwnerHistorySeeder::TX_PREFIX . '%')
            ->orWhere(fn ($q) => $q->whereNull('transaction_id')->where('status', Order::FAILED)->where('payment_driver', 'fake')));
    }

    public function testEveryRestaurantOfTheGroupHasOrdersEveryMonth()
    {
        $profile = CatalogPlan::profile()['owner_history'];
        $ids = $this->restaurantIds();
        $this->assertNotEmpty($ids);
        foreach ($ids as $id) {
            $months = DB::table('orders')->where('restaurant_id', $id)
                ->where('created_at', '>=', $profile['from'])->where('created_at', '<', '2026-11-01')
                ->get(['created_at'])->groupBy(fn ($o) => substr($o->created_at, 0, 7));
            $this->assertCount(2, $months, "restaurant {$id}: one group of orders per full month");
            foreach ($months as $month => $orders) {
                $this->assertGreaterThanOrEqual($profile['min_monthly'], $orders->count(), "restaurant {$id}, {$month}");
            }
        }
    }

    public function testOrdersAreCoherent()
    {
        $orders = $this->history()->with('plates')->get();
        $this->assertNotEmpty($orders);
        $ids = $this->restaurantIds();
        foreach ($orders as $order) {
            $this->assertContains($order->restaurant_id, $ids);
            $this->assertNotEmpty($order->plates);
            $this->assertTrue($order->plates->every(fn ($p) => $p->restaurant_id === $order->restaurant_id));
            $this->assertEqualsWithDelta($order->plates->sum(fn ($p) => $p->pivot->quantity * $p->pivot->unit_price), $order->price, 0.01);
            $this->assertTrue($order->created_at->lessThanOrEqualTo(DemoOwnerHistorySeeder::TO . ' 23:59:59'));
            if ($order->fulfillment_status === Order::DELIVERED) {
                $this->assertTrue($order->delivered_at->greaterThan($order->created_at));
            }
            // inside the opening hours of that weekday
            $minute = $order->created_at->hour * 60 + $order->created_at->minute;
            $open = DB::table('opening_hours')->where('restaurant_id', $order->restaurant_id)->where('weekday', $order->created_at->dayOfWeek)->get()
                ->contains(fn ($h) => $minute >= $this->minutes($h->opens_at) && ($this->minutes($h->closes_at) <= $this->minutes($h->opens_at) || $minute < $this->minutes($h->closes_at)));
            $hasHours = DB::table('opening_hours')->where('restaurant_id', $order->restaurant_id)->exists();
            $this->assertTrue($open || ! $hasHours, "order {$order->id} outside the opening hours");
        }
    }

    public function testAlmostEveryDeliveredOrderOfAnAccountIsReviewed()
    {
        $delivered = $this->history()->whereNotNull('user_id')->where('fulfillment_status', Order::DELIVERED);
        $total = (clone $delivered)->count();
        $reviewed = (clone $delivered)->has('review')->count();
        $this->assertGreaterThan(0, $total);
        $this->assertGreaterThan(0.6, $reviewed / $total);

        // reviews belong to the customer of the order, after the delivery
        $wrong = DB::table('reviews as v')->join('orders as o', 'o.id', '=', 'v.order_id')
            ->where('o.transaction_id', 'like', DemoOwnerHistorySeeder::TX_PREFIX . '%')
            ->where(fn ($q) => $q->whereColumn('v.user_id', '!=', 'o.user_id')->orWhereColumn('v.restaurant_id', '!=', 'o.restaurant_id')->orWhereColumn('v.created_at', '<', 'o.delivered_at'))
            ->count();
        $this->assertSame(0, $wrong);
    }

    public function testCustomersAreRegisteredBeforeTheirFirstOrder()
    {
        $users = User::whereIn('email', DemoOwnerHistorySeeder::customerEmails())->get();
        $this->assertNotEmpty($users);
        foreach ($users as $user) {
            $this->assertSame(User::CUSTOMER, $user->role);
            $first = Order::where('user_id', $user->id)->min('created_at');
            $this->assertNotNull($first, "{$user->email} has no orders");
            $this->assertTrue($user->created_at->lessThan($first));
        }
    }

    public function testRunningItAgainChangesNothing()
    {
        $before = [Order::count(), User::count(), DB::table('reviews')->count(), DB::table('order_plate')->count()];
        $this->seed(DemoOwnerHistorySeeder::class);
        $this->assertSame($before, [Order::count(), User::count(), DB::table('reviews')->count(), DB::table('order_plate')->count()]);
    }

    private function minutes(string $time): int
    {
        [$h, $m] = explode(':', $time);

        return (int) $h * 60 + (int) $m;
    }
}
