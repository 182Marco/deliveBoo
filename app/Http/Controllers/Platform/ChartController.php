<?php

namespace App\Http\Controllers\Platform;

use App\Analytics\ChartFormat;
use App\Analytics\OrderMetrics;
use App\Analytics\Period;
use App\Analytics\SqlBuckets;
use App\Http\Controllers\Controller;
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
        $partial = ChartFormat::partialBuckets($period, $unit, array_column($series, 'bucket'));
        $labels = ChartFormat::labels($period, $unit, array_column($series, 'bucket'), 'platform.charts.partial');

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
            'heatmap' => ChartFormat::heatmap(OrderMetrics::heatmap($period)),
            'charts' => $charts,
        ];
    }

    /** kept for the Tables views: same as ChartFormat::bucketLabel */
    public static function bucketLabel(string $bucket, string $unit): string
    {
        return ChartFormat::bucketLabel($bucket, $unit);
    }
}
