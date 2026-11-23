<?php

namespace Tests\Feature\Platform;

use App\Analytics\OrderMetrics;
use App\Analytics\Period;
use App\Analytics\SqlBuckets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Metric definitions on a small dataset with known numbers, in January 2030 (far from the seed).
 *
 *   R1 Testville (cuisine T): completed 10 (a), 20 (a), 30 (b) · in progress 15 (c) · cancelled 50 (d)
 *                             · failed 99 (e) · pending 1000 (ignored) · December 2029: completed 20 (a)
 *   R2 Otherville:            completed 40 (a)
 */
class OrderMetricsTest extends TestCase
{
    use RefreshDatabase;

    private int $r1;
    private int $r2;
    private int $type;

    protected function setUp(): void
    {
        parent::setUp();
        config(['platform.commission_rate' => 0.25]);

        $this->r1 = DB::table('restaurants')->insertGetId(['name' => 'Metric One', 'slug' => 'metric-one', 'phone' => '1', 'city' => 'Testville', 'address' => 'a', 'postal_code' => '9001']);
        $this->r2 = DB::table('restaurants')->insertGetId(['name' => 'Metric Two', 'slug' => 'metric-two', 'phone' => '1', 'city' => 'Otherville', 'address' => 'b', 'postal_code' => '9002']);
        $this->type = (int) DB::table('types')->value('id');
        DB::table('restaurant_type')->insert(['restaurant_id' => $this->r1, 'type_id' => $this->type]);
        DB::table('rates')->insert([['restaurant_id' => $this->r1, 'rate' => 4], ['restaurant_id' => $this->r1, 'rate' => 5]]);

        // Monday 2030-01-07 at 12:30 for the completed orders of the week
        $this->order($this->r1, 10, 'a', 'paid', 'delivered', '2030-01-07 12:30:00', 30);
        $this->order($this->r1, 20, 'a', 'paid', 'delivered', '2030-01-07 12:30:00', 40);
        $this->order($this->r1, 30, 'b', 'paid', 'delivered', '2030-01-08 20:15:00', 50);
        $this->order($this->r2, 40, 'a', 'paid', 'delivered', '2030-01-20 19:00:00', 20);
        $this->order($this->r1, 15, 'c', 'paid', 'received', '2030-01-31 21:00:00');
        $this->order($this->r1, 50, 'd', 'paid', 'cancelled', '2030-01-10 13:00:00');
        $this->order($this->r1, 99, 'e', 'failed', null, '2030-01-11 13:00:00');
        $this->order($this->r1, 1000, 'f', 'pending', null, '2030-01-12 13:00:00');
        $this->order($this->r1, 20, 'a', 'paid', 'delivered', '2029-12-15 12:00:00', 25);
    }

    private function order(int $restaurant, float $price, string $who, string $status, ?string $fulfillment, string $at, ?int $deliveryMinutes = null): void
    {
        DB::table('orders')->insert([
            'restaurant_id' => $restaurant, 'price' => $price, 'status' => $status, 'fulfillment_status' => $fulfillment,
            'customer_name' => $who, 'customer_lastName' => 'Test', 'customer_email' => "{$who}@metrics.test",
            'customer_phone' => '1', 'customer_address' => 'x',
            'created_at' => $at, 'updated_at' => $at,
            'delivered_at' => $deliveryMinutes ? date('Y-m-d H:i:s', strtotime($at) + 60 * $deliveryMinutes) : null,
        ]);
    }

    private function january(?string $city = null, ?int $type = null): Period
    {
        return Period::make('2030-01-01', '2030-01-31', $city, $type);
    }

    public function testTotalsFollowTheDefinitions()
    {
        $t = OrderMetrics::totals($this->january());

        $this->assertSame(4, $t['completed_orders']);
        $this->assertSame(100.0, $t['completed_value']);
        $this->assertSame(1, $t['in_progress_orders']);
        $this->assertSame(15.0, $t['in_progress_value']);
        $this->assertSame(1, $t['cancelled_orders']);
        $this->assertSame(50.0, $t['cancelled_value']);
        $this->assertSame(1, $t['failed_orders']);
        $this->assertSame(99.0, $t['failed_value']);
        // gross = completed + in progress; never cancelled, failed or pending
        $this->assertSame(5, $t['gross_orders']);
        $this->assertSame(115.0, $t['gross_value']);
        $this->assertSame(3, $t['customers']);
        $this->assertSame(25.0, $t['average_order']);
        // simulated commission on the completed value only
        $this->assertSame(25.0, $t['revenue']);
        $this->assertSame(round(1 / 6, 4), $t['cancel_rate']);
        $this->assertSame(round(1 / 7, 4), $t['failure_rate']);
    }

    public function testCommissionRateComesFromTheConfig()
    {
        config(['platform.commission_rate' => 0.15]);

        $this->assertSame(15.0, OrderMetrics::totals($this->january())['revenue']);
    }

    public function testFiltersByCityAndCuisine()
    {
        $city = OrderMetrics::totals($this->january('Testville'));
        $this->assertSame(3, $city['completed_orders']);
        $this->assertSame(60.0, $city['completed_value']);
        $this->assertSame(20.0, $city['average_order']);

        $this->assertSame(75.0, OrderMetrics::totals($this->january(null, $this->type))['gross_value']);
    }

    public function testSummaryComparesWithThePreviousPeriod()
    {
        $s = OrderMetrics::summary($this->january());

        $this->assertSame(['from' => '2029-12-01', 'to' => '2029-12-31'], $s['previous_period']);
        $this->assertSame(20.0, $s['previous']['completed_value']);
        $this->assertSame(4.0, $s['change']['completed_value']);
        // nothing failed before: no percentage instead of a division by zero
        $this->assertNull($s['change']['failed_orders']);
        // delivery minutes 20, 30, 40, 50: lower median and 90th percentile
        $this->assertSame(30, $s['current']['delivery_median_minutes']);
        $this->assertSame(40, $s['current']['delivery_p90_minutes']);
    }

    public function testSeriesFillEveryBucket()
    {
        $days = OrderMetrics::series($this->january(), 'day');
        $this->assertSame('day', $days['granularity']);
        $this->assertCount(31, $days['points']);
        $monday = collect($days['points'])->firstWhere('bucket', '2030-01-07');
        $this->assertSame(2, $monday['completed_orders']);
        $this->assertSame(30.0, $monday['completed_value']);
        $this->assertSame(0, collect($days['points'])->firstWhere('bucket', '2030-01-02')['completed_orders']);

        $weeks = OrderMetrics::series($this->january(), 'week');
        $this->assertSame('2029-12-31', $weeks['points'][0]['bucket']);
        $this->assertSame(3, $weeks['points'][1]['completed_orders'] + $weeks['points'][0]['completed_orders']);

        $months = OrderMetrics::series(Period::make('2029-12-01', '2030-01-31'), 'month');
        $this->assertSame(['2029-12', '2030-01'], array_column($months['points'], 'bucket'));
        $this->assertSame([20.0, 100.0], array_column($months['points'], 'completed_value'));
    }

    public function testHeatmapCountsCompletedOrdersByWeekdayAndHour()
    {
        $grid = OrderMetrics::heatmap($this->january());

        $this->assertSame(2, $grid[0][12]);   // Monday 12:00
        $this->assertSame(1, $grid[1][20]);   // Tuesday 20:00
        $this->assertSame(1, $grid[6][19]);   // Sunday 19:00
        $this->assertSame(4, array_sum(array_map('array_sum', $grid)));
    }

    public function testRankingsZonesAndCuisines()
    {
        $top = OrderMetrics::restaurants($this->january());
        $this->assertSame(['Metric One', 'Metric Two'], array_column($top, 'name'));
        $this->assertSame(75.0, $top[0]['gross_value']);
        $this->assertSame(4.5, $top[0]['rating']);
        $this->assertSame(2, $top[0]['ratings']);
        $this->assertNull($top[1]['rating']);

        $zones = OrderMetrics::zones($this->january());
        $this->assertSame(['Testville', 'Otherville'], array_column($zones, 'zone'));
        $this->assertSame(['9001', '9002'], array_column(OrderMetrics::zones($this->january(), 'postal_code'), 'zone'));

        $cuisines = OrderMetrics::cuisines($this->january());
        $this->assertCount(1, $cuisines);
        $this->assertSame(75.0, $cuisines[0]['gross_value']);
    }

    public function testNewAndReturningCustomers()
    {
        $c = OrderMetrics::customers($this->january(), 'month');

        // a ordered in December already: returning; b and c are new
        $this->assertSame([['bucket' => '2030-01', 'customers' => 3, 'new' => 2, 'returning' => 1]], $c['points']);
        $this->assertSame(['1' => 2, '2' => 0, '3-5' => 1, '6-10' => 0, '11+' => 0], $c['distribution']);
        $this->assertSame(round(1 / 3, 4), $c['repeat_rate']);
    }

    public function testRegistrationsByRole()
    {
        foreach ([['customer', 'c1'], ['customer', 'c2'], ['restaurateur', 'r1'], ['admin', 'x1']] as [$role, $mail]) {
            DB::table('users')->insert(['name' => $mail, 'last_name' => 'T', 'email' => "{$mail}@reg.test", 'role' => $role, 'created_at' => '2030-01-05 10:00:00']);
        }

        $r = OrderMetrics::registrations($this->january(), 'month');
        $this->assertSame([['bucket' => '2030-01', 'customer' => 2, 'restaurateur' => 1]], $r['points']);
    }

    public function testGranularityFollowsTheLength()
    {
        $this->assertSame('day', SqlBuckets::granularityFor(Period::make('2030-01-01', '2030-02-28')));
        $this->assertSame('week', SqlBuckets::granularityFor(Period::make('2030-01-01', '2030-05-31')));
        $this->assertSame('month', SqlBuckets::granularityFor(Period::make('2029-01-01', '2030-01-31')));
    }

    public function testEmptyPeriodHasNoDivisionsByZero()
    {
        $t = OrderMetrics::totals(Period::make('2031-01-01', '2031-01-31'));

        $this->assertSame(0, $t['completed_orders']);
        $this->assertNull($t['average_order']);
        $this->assertNull($t['cancel_rate']);
        $this->assertSame(0.0, $t['revenue']);
    }
}
