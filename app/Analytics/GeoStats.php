<?php

namespace App\Analytics;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Data of the Maps tab: only aggregates and the coordinates stored on the restaurants.
 * Orders have no position of their own (the customer address has no city), so every order is
 * placed on the restaurant that served it. Nothing is invented: a restaurant without coordinates
 * is listed apart, a zone is centred on the average of its geolocated restaurants.
 */
final class GeoStats
{
    public const MISSING_LIST = 20;

    public static function map(Period $period): array
    {
        return Cache::remember($period->cacheKey('map'), (int) config('platform.cache_seconds', 600), function () use ($period) {
            $sales = OrderMetrics::orders($period)->whereNotNull('o.restaurant_id')
                ->selectRaw('o.restaurant_id, '.OrderMetrics::aggregates())->groupBy('o.restaurant_id');
            $ratings = DB::table('rates')->selectRaw('restaurant_id, AVG(rate) AS avg_rate')->groupBy('restaurant_id');

            $rows = self::restaurants($period)
                ->leftJoinSub($sales, 's', 's.restaurant_id', '=', 'r.id')
                ->leftJoinSub($ratings, 'rt', 'rt.restaurant_id', '=', 'r.id')
                ->select('r.id', 'r.name', 'r.slug', 'r.city', 'r.postal_code', 'r.latitude', 'r.longitude', 'rt.avg_rate',
                    's.completed_orders', 's.completed_value', 's.in_progress_orders', 's.in_progress_value', 's.cancelled_orders',
                    's.cancelled_value', 's.failed_orders', 's.failed_value', 's.gross_orders', 's.gross_value', 's.customers')
                ->orderBy('r.name')->get();

            $points = [];
            $missing = [];
            foreach ($rows as $r) {
                $metrics = OrderMetrics::derive($r);
                $point = [
                    'id' => (int) $r->id,
                    'name' => $r->name,
                    'slug' => $r->slug,
                    'city' => $r->city,
                    'gross_value' => $metrics['gross_value'],
                    'completed_orders' => $metrics['completed_orders'],
                    'average_order' => $metrics['average_order'],
                    'revenue' => $metrics['revenue'],
                    'rating' => $r->avg_rate === null ? null : round((float) $r->avg_rate, 1),
                ];
                if ($r->latitude === null || $r->longitude === null) {
                    $missing[] = $point;
                } else {
                    $points[] = ['lat' => (float) $r->latitude, 'lng' => (float) $r->longitude, ...$point];
                }
            }

            return [
                'restaurants' => $points,
                'zones' => self::zones($points, $missing),
                'missing' => ['count' => count($missing), 'items' => array_slice(array_column($missing, 'name'), 0, self::MISSING_LIST)],
                'commission_rate' => OrderMetrics::commissionRate(),
            ];
        });
    }

    /** restaurants of the filters (city, cuisine), whether they sold or not */
    private static function restaurants(Period $period): Builder
    {
        return DB::table('restaurants as r')
            ->when($period->city, fn (Builder $q, string $city) => $q->where('r.city', $city))
            ->when($period->typeId, fn (Builder $q, int $type) => $q->whereExists(fn (Builder $e) => $e
                ->from('restaurant_type as t')->whereColumn('t.restaurant_id', 'r.id')->where('t.type_id', $type)));
    }

    /** one zone per city: totals of all its restaurants, centre = mean of the geolocated ones */
    private static function zones(array $points, array $missing): array
    {
        $zones = [];
        foreach ([...$points, ...$missing] as $p) {
            $z = &$zones[$p['city'] ?? ''];
            $z ??= ['zone' => $p['city'], 'restaurants' => 0, 'located' => 0, 'lat' => 0.0, 'lng' => 0.0, 'gross_value' => 0.0, 'completed_orders' => 0, 'revenue' => 0.0];
            $z['restaurants']++;
            $z['gross_value'] = round($z['gross_value'] + $p['gross_value'], 2);
            $z['completed_orders'] += $p['completed_orders'];
            $z['revenue'] = round($z['revenue'] + $p['revenue'], 2);
            if (isset($p['lat'])) {
                $z['located']++;
                $z['lat'] += $p['lat'];
                $z['lng'] += $p['lng'];
            }
            unset($z);
        }

        $out = [];
        foreach ($zones as $z) {
            $z['lat'] = $z['located'] ? round($z['lat'] / $z['located'], 6) : null;
            $z['lng'] = $z['located'] ? round($z['lng'] / $z['located'], 6) : null;
            $out[] = $z;
        }
        usort($out, fn ($a, $b) => $b['gross_value'] <=> $a['gross_value']);

        return $out;
    }
}
