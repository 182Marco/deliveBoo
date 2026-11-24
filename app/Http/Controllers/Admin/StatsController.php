<?php

namespace App\Http\Controllers\Admin;

use App\Analytics\ChartFormat;
use App\Analytics\OrderMetrics;
use App\Analytics\Period;
use App\Analytics\RestaurantMetrics;
use App\Analytics\SqlBuckets;
use App\Http\Controllers\Controller;
use App\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

// Partner statistics (dev/PIANO_STATISTICHE.md): all the restaurants of the logged owner (index) or one
// of them (show), over a period chosen by the owner. Same metric engine as the platform backoffice
// (App\Analytics), scoped by Period::forPartner after the ownership check: the page receives only
// aggregates, never the orders nor the contacts of the customers.
class StatsController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $owner = $request->user();
        // the scope selector of the page: one restaurant opens its own page (show checks the owner)
        $chosen = $request->query('restaurant');
        if (is_string($chosen) && ctype_digit($chosen)) {
            return redirect()->route('admin.stats.show', ['stat' => (int) $chosen, ...$request->only('from', 'to')]);
        }
        $restaurants = self::restaurantsOf($owner->id);

        return $this->page($restaurants, null, Period::forPartner($request, $owner->id));
    }

    public function show(Request $request, $restaurant_id): View
    {
        // SEC-2: statistics come from the orders, only the owner reads them
        $restaurant = Restaurant::findOrFail($restaurant_id);
        if ($request->user()->cannot('manage', $restaurant)) {
            return view('admin.notYours.orders');
        }

        return $this->page(self::restaurantsOf($restaurant->user_id), $restaurant, Period::forPartner($request, $restaurant->user_id, $restaurant->id));
    }

    private static function restaurantsOf(int $ownerId): Collection
    {
        return Restaurant::where('user_id', $ownerId)->orderBy('name')->get(['id', 'name', 'slug']);
    }

    private function page(Collection $restaurants, ?Restaurant $restaurant, Period $period): View
    {
        return view('admin.stats.show', [
            'period' => $period,
            'restaurant' => $restaurant,
            'restaurants' => $restaurants,
            ...($restaurants->isEmpty() ? ['empty' => true] : ['empty' => false, ...$this->build($period, $restaurant, $restaurants)]),
        ]);
    }

    private function build(Period $period, ?Restaurant $restaurant, Collection $restaurants): array
    {
        $unit = SqlBuckets::granularityFor($period);
        $summary = OrderMetrics::summary($period);
        $series = OrderMetrics::series($period, $unit)['points'];
        $previous = OrderMetrics::series($period->previous(), $unit)['points'];
        $customers = OrderMetrics::customers($period, $unit);
        $ratings = RestaurantMetrics::ratings($period, $unit);
        $delivery = RestaurantMetrics::deliverySeries($period, $unit);
        $rating = RestaurantMetrics::total($ratings);
        $ratingBefore = RestaurantMetrics::rating($period->previous());
        $dishes = RestaurantMetrics::topDishes($period);
        // the comparison table only makes sense for a group
        $table = $restaurant === null ? RestaurantMetrics::restaurants($period, $restaurants) : [];

        $c = fn (string $key, array $replace = []) => __("platform.charts.{$key}", $replace);
        $s = fn (string $key, array $replace = []) => __("partner.stats.{$key}", $replace);
        $buckets = array_column($series, 'bucket');
        $labels = ChartFormat::labels($period, $unit, $buckets, 'platform.charts.partial');
        $top = array_slice($table, 0, 10);

        $charts = [
            'sales' => ['type' => 'line', 'money' => true, 'labels' => $labels,
                'datasets' => [['label' => $s('kpi.gross'), 'data' => array_column($series, 'gross_value')]]],
            'orders' => ['type' => 'bar', 'labels' => $labels,
                'datasets' => [['label' => $c('col.orders'), 'data' => array_column($series, 'completed_orders')]]],
            'compare' => ['type' => 'line', 'money' => true, 'labels' => $labels, 'datasets' => [
                ['label' => $c('compare.current'), 'data' => array_column($series, 'gross_value')],
                ['label' => $c('compare.previous'), 'data' => array_slice(array_pad(array_column($previous, 'gross_value'), count($series), null), 0, count($series)), 'muted' => true],
            ]],
            'ratings' => ['type' => 'line', 'labels' => $labels,
                'datasets' => [['label' => $s('ratings.average'), 'data' => array_column($ratings, 'average')]]],
            'delivery' => ['type' => 'line', 'labels' => $labels,
                'datasets' => [['label' => $s('delivery.minutes'), 'data' => array_column($delivery, 'minutes')]]],
            'customers' => ['type' => 'bar', 'stacked' => true, 'labels' => $labels, 'datasets' => [
                ['label' => $c('customers.new'), 'data' => array_column($customers['points'], 'new')],
                ['label' => $c('customers.returning'), 'data' => array_column($customers['points'], 'returning')],
            ]],
            'dishes' => ['type' => 'bar', 'horizontal' => true, 'labels' => array_column($dishes, 'name'),
                'datasets' => [['label' => $s('dishes.portions'), 'data' => array_column($dishes, 'quantity')]]],
        ];
        if ($top) {
            $charts['restaurants'] = ['type' => 'bar', 'horizontal' => true, 'money' => true, 'labels' => array_column($top, 'name'),
                'datasets' => [['label' => $s('kpi.gross'), 'data' => array_column($top, 'gross_value')]]];
        }

        return [
            'unit' => $unit,
            'unitLabel' => $c("unit.{$unit}"),
            'hasPartial' => ChartFormat::partialBuckets($period, $unit, $buckets) !== [],
            'summary' => $summary,
            'rating' => $rating,
            'ratingChange' => $rating['average'] !== null && $ratingBefore['average']
                ? round(($rating['average'] - $ratingBefore['average']) / $ratingBefore['average'], 4) : null,
            'series' => $series,
            'labels' => $labels,
            'ratings' => $ratings,
            'delivery' => $delivery,
            'customers' => $customers,
            'dishes' => $dishes,
            'fans' => RestaurantMetrics::topCustomers($period),
            'table' => $table,
            'heatmap' => ChartFormat::heatmap(OrderMetrics::heatmap($period)),
            'charts' => $charts,
        ];
    }
}
