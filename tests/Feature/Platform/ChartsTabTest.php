<?php

namespace Tests\Feature\Platform;

use App\Analytics\OrderMetrics;
use App\Analytics\Period;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tab "Charts" of /platform: KPIs, chart data and tables from the real (seeded) orders (step 4.2). */
class ChartsTabTest extends TestCase
{
    use RefreshDatabase;

    private const CHARTS = ['sales', 'orders', 'average', 'compare', 'top', 'cities', 'cuisines', 'customers', 'distribution', 'registrations'];

    private function admin(): User
    {
        return User::where('role', User::ADMIN)->firstOrFail();
    }

    private function allTime(): array
    {
        return Period::presets()['all'];
    }

    public function testThePageShowsKpisChartsAndTables()
    {
        $response = $this->actingAs($this->admin())->get('/platform/charts?'.http_build_query($this->allTime()))->assertOk();

        $response->assertSee(__('platform.charts.kpi.gross'))
            ->assertSee(__('platform.charts.kpi.revenue'))
            ->assertSee(__('platform.charts.estimate'))
            ->assertSee('id="platform-charts"', false)
            ->assertSee('data-chart="sales"', false)
            ->assertSee(__('platform.charts.heatmap.title'))
            ->assertSee(__('platform.charts.table'));
        $this->assertSame(self::CHARTS, array_keys($response->viewData('charts')));
    }

    public function testTheJsonMatchesTheMetrics()
    {
        $range = $this->allTime();
        $json = $this->actingAs($this->admin())->getJson('/platform/data/charts?'.http_build_query($range))->assertOk()->json();

        $totals = OrderMetrics::totals(Period::make($range['from'], $range['to']));
        $this->assertSame($totals['gross_value'], $json['summary']['current']['gross_value']);
        $this->assertSame($totals['revenue'], $json['summary']['current']['revenue']);
        $this->assertSame(self::CHARTS, array_keys($json['charts']));
        // the series add up to the totals: nothing lost between buckets
        $this->assertEqualsWithDelta($totals['gross_value'], array_sum($json['charts']['sales']['datasets'][0]['data']), 0.01);
        $this->assertSame($totals['completed_orders'], array_sum($json['charts']['orders']['datasets'][0]['data']));
        // no personal data in the payload
        $this->assertStringNotContainsString('@', json_encode($json['charts']));
    }

    public function testFiltersChangeTheNumbers()
    {
        $range = $this->allTime();
        $all = $this->actingAs($this->admin())->getJson('/platform/data/charts?'.http_build_query($range))->json('summary.current.gross_value');
        $city = Period::cities()[0];
        $one = $this->getJson('/platform/data/charts?'.http_build_query([...$range, 'city' => $city]))->json('summary.current.gross_value');

        $this->assertGreaterThan(0, $one);
        $this->assertLessThan($all, $one);
    }

    public function testAnEmptyPeriodShowsTheEmptyState()
    {
        $this->actingAs($this->admin())->get('/platform/charts?from=2000-01-01&to=2000-01-31')
            ->assertOk()->assertSee(__('platform.charts.empty'))->assertDontSee('id="platform-charts"', false);
    }

    public function testOnlyAdminsGetTheData()
    {
        $this->getJson('/platform/data/charts')->assertUnauthorized();
        $customer = User::where('role', User::CUSTOMER)->firstOrFail();
        $this->actingAs($customer)->getJson('/platform/data/charts')->assertForbidden();
    }
}
