<?php

namespace App\Http\Controllers\Platform;

use App\Analytics\OrderMetrics;
use App\Analytics\Period;
use App\Analytics\SqlBuckets;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// tab "Charts" of the platform backoffice. The KPIs and the tables are rendered here; the charts
// (Chart.js, resources/js/jsBack/platform/charts.js) get the same aggregated series as JSON.
class ChartController extends Controller
{
    public function index(Request $request): View
    {
        $period = Period::fromRequest($request);

        return view('platform.charts', ['period' => $period, ...$this->build($period)]);
    }

    /** the same data as JSON (GET /platform/data/charts), for scripts and tests */
    public function data(Request $request): JsonResponse
    {
        $data = $this->build(Period::fromRequest($request));

        return response()->json(['summary' => $data['summary'], 'charts' => $data['charts']]);
    }

    private function build(Period $period): array
    {
        $unit = SqlBuckets::granularityFor($period);
        $summary = OrderMetrics::summary($period);
        $series = OrderMetrics::series($period, $unit)['points'];
        $previous = OrderMetrics::series($period->previous(), $unit)['points'];
        $customers = OrderMetrics::customers($period, $unit);
        $registrations = OrderMetrics::registrations($period, $unit)['points'];
        $top = OrderMetrics::restaurants($period, 'gross_value', 10);
        $cities = OrderMetrics::zones($period, 'city');
        $cuisines = array_slice(OrderMetrics::cuisines($period), 0, 10);
        $text = fn (string $key, array $replace = []) => __("platform.charts.{$key}", $replace);
        // a week or month cut by the period would look like a collapse: say it is partial
        $partial = self::partialBuckets($period, $unit, array_column($series, 'bucket'));
        $labels = array_map(fn ($p) => in_array($p['bucket'], $partial, true)
            ? $text('partial', ['label' => self::bucketLabel($p['bucket'], $unit)])
            : self::bucketLabel($p['bucket'], $unit), $series);

        $charts = [
            'sales' => ['type' => 'line', 'money' => true, 'labels' => $labels,
                'datasets' => [['label' => $text('col.gross'), 'data' => array_column($series, 'gross_value')]]],
            'orders' => ['type' => 'bar', 'labels' => $labels,
                'datasets' => [['label' => $text('col.orders'), 'data' => array_column($series, 'completed_orders')]]],
            'average' => ['type' => 'line', 'money' => true, 'labels' => $labels,
                'datasets' => [['label' => $text('col.average'), 'data' => array_column($series, 'average_order')]]],
            'compare' => ['type' => 'line', 'money' => true, 'labels' => $labels, 'datasets' => [
                ['label' => $text('compare.current'), 'data' => array_column($series, 'gross_value')],
                ['label' => $text('compare.previous'), 'data' => array_slice(array_pad(array_column($previous, 'gross_value'), count($series), null), 0, count($series)), 'muted' => true],
            ]],
            'top' => ['type' => 'bar', 'horizontal' => true, 'money' => true, 'labels' => array_column($top, 'name'),
                'datasets' => [['label' => $text('col.gross'), 'data' => array_column($top, 'gross_value')]]],
            'cities' => ['type' => 'bar', 'horizontal' => true, 'money' => true, 'labels' => array_column($cities, 'zone'),
                'datasets' => [['label' => $text('col.gross'), 'data' => array_column($cities, 'gross_value')]]],
            'cuisines' => ['type' => 'bar', 'horizontal' => true, 'money' => true, 'labels' => array_column($cuisines, 'name'),
                'datasets' => [['label' => $text('col.gross'), 'data' => array_column($cuisines, 'gross_value')]]],
            'customers' => ['type' => 'bar', 'stacked' => true, 'labels' => $labels, 'datasets' => [
                ['label' => $text('customers.new'), 'data' => array_column($customers['points'], 'new')],
                ['label' => $text('customers.returning'), 'data' => array_column($customers['points'], 'returning')],
            ]],
            'distribution' => ['type' => 'bar', 'labels' => array_keys($customers['distribution']),
                'datasets' => [['label' => $text('col.customers'), 'data' => array_values($customers['distribution'])]]],
            'registrations' => ['type' => 'line', 'labels' => $labels, 'datasets' => [
                ['label' => $text('registrations.customer'), 'data' => array_column($registrations, 'customer')],
                ['label' => $text('registrations.restaurateur'), 'data' => array_column($registrations, 'restaurateur')],
            ]],
        ];

        return [
            'unit' => $unit,
            'unitLabel' => $text("unit.{$unit}"),
            'hasPartial' => $partial !== [],
            'summary' => $summary,
            'series' => $series,
            'labels' => $labels,
            'top' => $top,
            'cities' => $cities,
            'cuisines' => $cuisines,
            'customers' => $customers,
            'registrations' => $registrations,
            'hasRegistrations' => array_sum(array_map(fn ($p) => $p['customer'] + $p['restaurateur'], $registrations)) > 0,
            'heatmap' => self::heatmap(OrderMetrics::heatmap($period)),
            'charts' => $charts,
        ];
    }

    /** 7×24 cells with a level 0–5 of the one-hue scale (0 = no orders) and the localized weekday */
    private static function heatmap(array $grid): array
    {
        $max = max(1, ...array_map(fn ($row) => max($row), $grid));
        $monday = CarbonImmutable::parse('2030-01-07')->locale(app()->getLocale());
        $rows = [];
        foreach ($grid as $d => $hours) {
            $rows[] = [
                'day' => $monday->addDays($d)->isoFormat('ddd'),
                'cells' => array_map(fn ($n) => ['count' => $n, 'level' => $n ? (int) ceil(5 * $n / $max) : 0], $hours),
            ];
        }

        return ['rows' => $rows, 'max' => $max];
    }

    /** buckets (first and last) that start before or end after the period */
    private static function partialBuckets(Period $period, string $unit, array $buckets): array
    {
        if ($unit === 'day' || $buckets === []) {
            return [];
        }
        $start = fn (string $b) => CarbonImmutable::parse($unit === 'month' ? "{$b}-01" : $b);
        $end = fn (string $b) => $unit === 'month' ? $start($b)->endOfMonth() : $start($b)->addDays(6)->endOfDay();
        $first = $buckets[0];
        $last = $buckets[array_key_last($buckets)];

        return array_values(array_unique(array_filter([
            $start($first)->lessThan($period->from) ? $first : null,
            $end($last)->greaterThan($period->to) ? $last : null,
        ])));
    }

    public static function bucketLabel(string $bucket, string $unit): string
    {
        $date = CarbonImmutable::parse($unit === 'month' ? "{$bucket}-01" : $bucket)->locale(app()->getLocale());

        return $unit === 'month' ? $date->isoFormat('MMM YYYY') : $date->isoFormat('D MMM');
    }
}
