<?php

namespace App\Analytics;

use App\Plate;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * The metrics of the partner statistics that the platform backoffice does not have: stars, delivery
 * time, dishes and customers of the restaurants of a Period scope (all the restaurants of an owner
 * or one restaurant). The order metrics themselves stay in OrderMetrics, with the same definitions.
 *
 * - every number is computed in SQL on the orders of the period (OrderMetrics::orders), so a group is
 *   never an average of averages nor a sum of distinct customers: it is the same query on more rows;
 * - reviews count by the date of their order, hidden ones (moderation) excluded, every review counts
 *   (the public average keeps only the latest of each customer, a trend needs every order);
 * - nothing personal leaves this class: the best customers are a first name and an initial.
 */
final class RestaurantMetrics
{
    /** average stars and number of reviews of the orders of the period */
    public static function rating(Period $period): array
    {
        return self::cached($period, 'rating', function () use ($period) {
            $row = self::reviews($period)->selectRaw('AVG(v.rating) AS average, COUNT(*) AS n')->first();

            return ['average' => $row->n ? round((float) $row->average, 2) : null, 'count' => (int) $row->n];
        });
    }

    /** the rating of the whole period from the points of ratings(): total stars / reviews (exact, no extra query) */
    public static function total(array $points): array
    {
        $count = array_sum(array_column($points, 'count'));

        return ['average' => $count ? round(array_sum(array_column($points, 'stars')) / $count, 2) : null, 'count' => $count];
    }

    /** average stars and reviews per bucket, gaps as null (no reviews is not zero stars) */
    public static function ratings(Period $period, string $unit): array
    {
        return self::cached($period, "ratings:{$unit}", function () use ($period, $unit) {
            $bucket = SqlBuckets::period($unit, 'o.created_at');
            $rows = self::reviews($period)->selectRaw("{$bucket} AS bucket, SUM(v.rating) AS stars, COUNT(*) AS n")
                ->groupByRaw($bucket)->get()->keyBy('bucket');

            return array_map(fn ($key) => [
                'bucket' => $key,
                'average' => isset($rows[$key]) ? round((int) $rows[$key]->stars / (int) $rows[$key]->n, 2) : null,
                'count' => (int) ($rows[$key]->n ?? 0),
                'stars' => (int) ($rows[$key]->stars ?? 0),
            ], OrderMetrics::buckets($period, $unit));
        });
    }

    /** average minutes from order to delivery per bucket (completed orders), gaps as null */
    public static function deliverySeries(Period $period, string $unit): array
    {
        return self::cached($period, "delivery:{$unit}", function () use ($period, $unit) {
            $bucket = SqlBuckets::period($unit, 'o.created_at');
            $minutes = SqlBuckets::minutesBetween('o.created_at', 'o.delivered_at');
            $rows = self::delivered($period)->selectRaw("{$bucket} AS bucket, AVG({$minutes}) AS minutes")
                ->groupByRaw($bucket)->get()->keyBy('bucket');

            return array_map(fn ($key) => [
                'bucket' => $key,
                'minutes' => isset($rows[$key]) ? (int) round((float) $rows[$key]->minutes) : null,
            ], OrderMetrics::buckets($period, $unit));
        });
    }

    /**
     * the best selling dishes (drinks excluded) of the completed orders, by name: in a group the
     * same dish of several branches is one line
     */
    public static function topDishes(Period $period, int $limit = 10): array
    {
        return self::cached($period, "dishes:{$limit}", fn () => OrderMetrics::orders($period)->whereRaw(OrderMetrics::COMPLETED)
            ->join('order_plate as op', 'op.order_id', '=', 'o.id')
            ->join('plates as p', 'p.id', '=', 'op.plate_id')
            ->where(fn (Builder $q) => $q->whereNull('p.course')->orWhereNotIn('p.course', Plate::DRINK_COURSES))
            ->selectRaw('p.name, SUM(op.quantity) AS quantity, SUM(op.quantity * op.unit_price) AS value')
            ->groupBy('p.name')->orderByDesc('quantity')->orderBy('p.name')->limit($limit)->get()
            ->map(fn ($d) => ['name' => $d->name, 'quantity' => (int) $d->quantity, 'value' => round((float) $d->value, 2)])
            ->all());
    }

    /**
     * the customers who spent most (gross orders). The email only groups the orders inside SQL: the
     * result has a first name with the initial of the last name, the orders and the amount
     */
    public static function topCustomers(Period $period, int $limit = 5): array
    {
        return self::cached($period, "customers-top:{$limit}", fn () => OrderMetrics::orders($period)->whereRaw(OrderMetrics::GROSS)
            ->selectRaw('MAX(o.customer_name) AS name, MAX(o.customer_lastName) AS last_name, COUNT(*) AS orders, SUM(o.price) AS spent')
            ->groupBy('o.customer_email')->orderByDesc('spent')->orderByDesc('orders')->limit($limit)->get()
            ->map(fn ($c) => [
                'name' => self::shortName((string) $c->name, (string) $c->last_name),
                'orders' => (int) $c->orders,
                'spent' => round((float) $c->spent, 2),
            ])->all());
    }

    /**
     * one row per restaurant of the group (also the ones without orders in the period): the order
     * metrics of OrderMetrics::byRestaurant plus stars and delivery minutes, three queries in all
     *
     * @param  iterable<object{id: int, name: string, slug: string}>  $restaurants
     */
    public static function restaurants(Period $period, iterable $restaurants): array
    {
        $sales = OrderMetrics::byRestaurant($period);
        [$stars, $minutes] = self::cached($period, 'restaurants-extra', function () use ($period) {
            $delivery = SqlBuckets::minutesBetween('o.created_at', 'o.delivered_at');

            return [
                self::reviews($period)->selectRaw('o.restaurant_id AS id, AVG(v.rating) AS average, COUNT(*) AS n')
                    ->groupBy('o.restaurant_id')->get()->mapWithKeys(fn ($r) => [(int) $r->id => ['average' => round((float) $r->average, 2), 'count' => (int) $r->n]])->all(),
                self::delivered($period)->selectRaw("o.restaurant_id AS id, AVG({$delivery}) AS minutes")
                    ->groupBy('o.restaurant_id')->pluck('minutes', 'id')->map(fn ($m) => (int) round((float) $m))->all(),
            ];
        });

        $rows = [];
        foreach ($restaurants as $r) {
            $rows[] = [
                'id' => (int) $r->id,
                'name' => $r->name,
                'slug' => $r->slug,
                ...OrderMetrics::derive($sales[(int) $r->id] ?? null),
                'rating' => $stars[(int) $r->id]['average'] ?? null,
                'reviews' => $stars[(int) $r->id]['count'] ?? 0,
                'delivery_minutes' => $minutes[(int) $r->id] ?? null,
            ];
        }
        usort($rows, fn ($a, $b) => [$b['gross_value'], $a['name']] <=> [$a['gross_value'], $b['name']]);

        return $rows;
    }

    /** "Giulia R." */
    public static function shortName(string $first, string $last): string
    {
        $initial = mb_strtoupper(mb_substr(trim($last), 0, 1));

        return trim(trim($first).($initial !== '' ? " {$initial}." : ''));
    }

    private static function reviews(Period $period): Builder
    {
        return OrderMetrics::orders($period)->join('reviews as v', 'v.order_id', '=', 'o.id')->whereNull('v.hidden_at');
    }

    private static function delivered(Period $period): Builder
    {
        return OrderMetrics::orders($period)->whereRaw(OrderMetrics::COMPLETED)->whereNotNull('o.delivered_at');
    }

    private static function cached(Period $period, string $name, \Closure $compute): mixed
    {
        return Cache::remember($period->cacheKey($name), (int) config('platform.cache_seconds', 600), $compute);
    }
}
