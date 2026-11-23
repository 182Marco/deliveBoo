<?php

namespace App\Analytics;

use App\Order;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The one place where the metrics of the platform backoffice are defined (dev/PIANO_AUTH_CLIENTI.md,
 * "Metriche"). Everything is aggregated in SQL: the browser only receives the results.
 *
 * - completed orders   paid and delivered
 * - in progress        paid, not delivered nor cancelled yet (received, preparing, out for delivery)
 * - gross value (GMV)  value of the paid orders that were not cancelled: completed + in progress
 * - cancelled value    paid orders cancelled afterwards (cancelled is not the same as refunded)
 * - failed payments    attempts refused by the payment provider: never part of any revenue
 * - average order      completed value / completed orders
 * - platform revenue   ESTIMATE: simulated commission (config platform.commission_rate) × completed value
 * Pending orders (payment never finished) are left out of everything.
 * Zones and cuisines are the ones of the restaurant: orders store no customer location.
 */
final class OrderMetrics
{
    public const COMPLETED = "(o.status = 'paid' AND o.fulfillment_status = 'delivered')";
    public const IN_PROGRESS = "(o.status = 'paid' AND (o.fulfillment_status IS NULL OR o.fulfillment_status IN ('received', 'preparing', 'out_for_delivery')))";
    public const CANCELLED = "(o.status = 'paid' AND o.fulfillment_status = 'cancelled')";
    public const FAILED = "(o.status = 'failed')";
    public const GROSS = "(o.status = 'paid' AND (o.fulfillment_status IS NULL OR o.fulfillment_status <> 'cancelled'))";

    public static function commissionRate(): float
    {
        return (float) config('platform.commission_rate');
    }

    /** orders of the period (o) with their restaurant (r), filtered by city and cuisine */
    public static function orders(Period $period): Builder
    {
        return DB::table('orders as o')
            ->leftJoin('restaurants as r', 'r.id', '=', 'o.restaurant_id')
            ->whereBetween('o.created_at', [$period->from, $period->to])
            ->whereIn('o.status', [Order::PAID, Order::FAILED])
            ->when($period->city, fn (Builder $q, string $city) => $q->where('r.city', $city))
            ->when($period->typeId, fn (Builder $q, int $type) => $q->whereExists(fn (Builder $e) => $e
                ->from('restaurant_type as rt')->whereColumn('rt.restaurant_id', 'o.restaurant_id')->where('rt.type_id', $type)));
    }

    /** the aggregate columns shared by totals, series and rankings */
    public static function aggregates(): string
    {
        return implode(', ', [
            'SUM(CASE WHEN '.self::COMPLETED.' THEN 1 ELSE 0 END) AS completed_orders',
            'SUM(CASE WHEN '.self::COMPLETED.' THEN o.price ELSE 0 END) AS completed_value',
            'SUM(CASE WHEN '.self::IN_PROGRESS.' THEN 1 ELSE 0 END) AS in_progress_orders',
            'SUM(CASE WHEN '.self::IN_PROGRESS.' THEN o.price ELSE 0 END) AS in_progress_value',
            'SUM(CASE WHEN '.self::CANCELLED.' THEN 1 ELSE 0 END) AS cancelled_orders',
            'SUM(CASE WHEN '.self::CANCELLED.' THEN o.price ELSE 0 END) AS cancelled_value',
            'SUM(CASE WHEN '.self::FAILED.' THEN 1 ELSE 0 END) AS failed_orders',
            'SUM(CASE WHEN '.self::FAILED.' THEN o.price ELSE 0 END) AS failed_value',
            'SUM(CASE WHEN '.self::GROSS.' THEN 1 ELSE 0 END) AS gross_orders',
            'SUM(CASE WHEN '.self::GROSS.' THEN o.price ELSE 0 END) AS gross_value',
            'COUNT(DISTINCT CASE WHEN '.self::GROSS.' THEN o.customer_email END) AS customers',
        ]);
    }

    /** numbers of one aggregate row, with the derived metrics */
    public static function derive(object|array|null $row): array
    {
        $row = (array) ($row ?? []);
        $int = fn (string $k) => (int) ($row[$k] ?? 0);
        $money = fn (string $k) => round((float) ($row[$k] ?? 0), 2);

        $completed = $int('completed_orders');
        $paid = $completed + $int('in_progress_orders') + $int('cancelled_orders');
        $attempts = $paid + $int('failed_orders');

        return [
            'completed_orders' => $completed,
            'completed_value' => $money('completed_value'),
            'in_progress_orders' => $int('in_progress_orders'),
            'in_progress_value' => $money('in_progress_value'),
            'cancelled_orders' => $int('cancelled_orders'),
            'cancelled_value' => $money('cancelled_value'),
            'failed_orders' => $int('failed_orders'),
            'failed_value' => $money('failed_value'),
            'gross_orders' => $int('gross_orders'),
            'gross_value' => $money('gross_value'),
            'customers' => $int('customers'),
            'average_order' => $completed ? round((float) $row['completed_value'] / $completed, 2) : null,
            'revenue' => round((float) ($row['completed_value'] ?? 0) * self::commissionRate(), 2),
            'cancel_rate' => $paid ? round($int('cancelled_orders') / $paid, 4) : null,
            'failure_rate' => $attempts ? round($int('failed_orders') / $attempts, 4) : null,
        ];
    }

    /** totals of the period, of the previous period of the same length and the change in % */
    public static function summary(Period $period): array
    {
        return self::cached($period, 'summary', function () use ($period) {
            $current = self::totals($period);
            $previous = self::totals($period->previous());
            $change = [];
            foreach ($current as $key => $value) {
                $before = $previous[$key] ?? null;
                $change[$key] = is_numeric($value) && is_numeric($before) && (float) $before !== 0.0
                    ? round(($value - $before) / abs($before), 4)
                    : null;
            }

            return [
                'current' => $current + self::deliveryTimes($period),
                'previous' => $previous,
                'change' => $change,
                'previous_period' => ['from' => $period->previous()->from->toDateString(), 'to' => $period->previous()->to->toDateString()],
                'commission_rate' => self::commissionRate(),
            ];
        });
    }

    public static function totals(Period $period): array
    {
        return self::derive(self::orders($period)->selectRaw(self::aggregates())->first());
    }

    /** median and 90th percentile of the minutes from order to delivery (completed orders) */
    public static function deliveryTimes(Period $period): array
    {
        $minutes = SqlBuckets::minutesBetween('o.created_at', 'o.delivered_at');
        $base = fn () => self::orders($period)->whereRaw(self::COMPLETED)->whereNotNull('o.delivered_at');
        $count = $base()->count();
        $at = fn (float $q) => $count
            ? (int) $base()->selectRaw("{$minutes} AS m")->orderBy('m')->offset((int) floor(($count - 1) * $q))->limit(1)->value('m')
            : null;

        return ['delivery_median_minutes' => $at(0.5), 'delivery_p90_minutes' => $at(0.9)];
    }

    /** one point per day, week or month, gaps filled with zeros */
    public static function series(Period $period, ?string $granularity = null): array
    {
        $granularity ??= SqlBuckets::granularityFor($period);

        return self::cached($period, "series:{$granularity}", function () use ($period, $granularity) {
            $bucket = SqlBuckets::period($granularity, 'o.created_at');
            $rows = self::orders($period)
                ->selectRaw("{$bucket} AS bucket, ".self::aggregates())
                ->groupByRaw($bucket)->get()->keyBy('bucket');

            $points = [];
            foreach (self::buckets($period, $granularity) as $key) {
                $points[] = ['bucket' => $key, ...self::derive($rows[$key] ?? null)];
            }

            return ['granularity' => $granularity, 'points' => $points];
        });
    }

    /** completed orders by weekday (0 = Monday) and hour */
    public static function heatmap(Period $period): array
    {
        return self::cached($period, 'heatmap', function () use ($period) {
            $day = SqlBuckets::weekday('o.created_at');
            $hour = SqlBuckets::hour('o.created_at');
            $grid = array_fill(0, 7, array_fill(0, 24, 0));
            foreach (self::orders($period)->whereRaw(self::COMPLETED)
                ->selectRaw("{$day} AS d, {$hour} AS h, COUNT(*) AS n")->groupByRaw("{$day}, {$hour}")->get() as $cell) {
                $grid[(int) $cell->d][(int) $cell->h] = (int) $cell->n;
            }

            return $grid;
        });
    }

    /** restaurants ranked by a metric; rating = average of all their rates (rates have no period) */
    public static function restaurants(Period $period, string $sort = 'gross_value', int $limit = 10): array
    {
        $sort = in_array($sort, ['gross_value', 'completed_orders', 'average_order', 'revenue', 'cancel_rate'], true) ? $sort : 'gross_value';

        return self::cached($period, "restaurants:{$sort}:{$limit}", function () use ($period, $sort, $limit) {
            $rows = self::orders($period)->whereNotNull('r.id')
                ->selectRaw('r.id, r.name, r.slug, r.city, '.self::aggregates())
                ->groupBy('r.id', 'r.name', 'r.slug', 'r.city')->get();
            $ratings = DB::table('rates')->whereIn('restaurant_id', $rows->pluck('id'))
                ->groupBy('restaurant_id')->selectRaw('restaurant_id, AVG(rate) AS avg_rate, COUNT(*) AS n')->get()->keyBy('restaurant_id');

            return $rows->map(fn ($r) => [
                'id' => (int) $r->id, 'name' => $r->name, 'slug' => $r->slug, 'city' => $r->city,
                ...self::derive($r),
                'rating' => isset($ratings[$r->id]) ? round((float) $ratings[$r->id]->avg_rate, 2) : null,
                'ratings' => (int) ($ratings[$r->id]->n ?? 0),
            ])->sortByDesc(fn ($r) => $r[$sort] ?? -1)->take($limit)->values()->all();
        });
    }

    /** by city or postal code of the restaurant */
    public static function zones(Period $period, string $by = 'city'): array
    {
        $column = $by === 'postal_code' ? 'r.postal_code' : 'r.city';

        return self::cached($period, "zones:{$by}", fn () => self::orders($period)->whereNotNull($column)
            ->selectRaw("{$column} AS zone, COUNT(DISTINCT r.id) AS restaurants, ".self::aggregates())
            ->groupBy($column)->get()
            ->map(fn ($z) => ['zone' => $z->zone, 'restaurants' => (int) $z->restaurants, ...self::derive($z)])
            ->sortByDesc('gross_value')->values()->all());
    }

    /**
     * by cuisine. A restaurant with several cuisines counts in each of them, so the sum of the
     * cuisines can be higher than the total (declared in the interface).
     */
    public static function cuisines(Period $period): array
    {
        return self::cached($period, 'cuisines', fn () => self::orders($period)
            ->join('restaurant_type as rt2', 'rt2.restaurant_id', '=', 'o.restaurant_id')
            ->join('types as t', 't.id', '=', 'rt2.type_id')
            ->selectRaw('t.id, t.name, '.self::aggregates())
            ->groupBy('t.id', 't.name')->get()
            ->map(fn ($c) => ['id' => (int) $c->id, 'name' => $c->name, ...self::derive($c)])
            ->sortByDesc('gross_value')->values()->all());
    }

    /**
     * customers (by email: guests have no account) per bucket: "new" ordered for the first time
     * ever in that bucket, "returning" had ordered before. Plus the repeat rate of the period.
     */
    public static function customers(Period $period, ?string $granularity = null): array
    {
        $granularity ??= SqlBuckets::granularityFor($period);

        return self::cached($period, "customers:{$granularity}", function () use ($period, $granularity) {
            $first = DB::table('orders')->where('status', Order::PAID)
                ->selectRaw('customer_email, MIN(created_at) AS first_at')->groupBy('customer_email');
            $bucket = SqlBuckets::period($granularity, 'o.created_at');
            $firstBucket = SqlBuckets::period($granularity, 'f.first_at');
            $rows = self::orders($period)->whereRaw(self::GROSS)
                ->joinSub($first, 'f', 'f.customer_email', '=', 'o.customer_email')
                ->selectRaw("{$bucket} AS bucket, COUNT(DISTINCT o.customer_email) AS customers, COUNT(DISTINCT CASE WHEN {$firstBucket} = {$bucket} THEN o.customer_email END) AS new_customers")
                ->groupByRaw($bucket)->get()->keyBy('bucket');

            $points = [];
            foreach (self::buckets($period, $granularity) as $key) {
                $all = (int) ($rows[$key]->customers ?? 0);
                $new = (int) ($rows[$key]->new_customers ?? 0);
                $points[] = ['bucket' => $key, 'customers' => $all, 'new' => $new, 'returning' => $all - $new];
            }

            // how many orders each customer placed in the period
            $perCustomer = self::orders($period)->whereRaw(self::GROSS)
                ->selectRaw('o.customer_email, COUNT(*) AS n')->groupBy('o.customer_email')->pluck('n')->map(fn ($n) => (int) $n);
            $bands = ['1' => 0, '2' => 0, '3-5' => 0, '6-10' => 0, '11+' => 0];
            foreach ($perCustomer as $n) {
                $bands[match (true) { $n === 1 => '1', $n === 2 => '2', $n <= 5 => '3-5', $n <= 10 => '6-10', default => '11+' }]++;
            }

            return [
                'granularity' => $granularity,
                'points' => $points,
                'distribution' => $bands,
                'repeat_rate' => $perCustomer->count() ? round($perCustomer->filter(fn ($n) => $n > 1)->count() / $perCustomer->count(), 4) : null,
            ];
        });
    }

    /** accounts created per bucket and role (customers, restaurateurs; admins are not a market metric) */
    public static function registrations(Period $period, ?string $granularity = null): array
    {
        $granularity ??= SqlBuckets::granularityFor($period);

        return self::cached($period, "registrations:{$granularity}", function () use ($period, $granularity) {
            $bucket = SqlBuckets::period($granularity, 'created_at');
            $rows = DB::table('users')->whereBetween('created_at', [$period->from, $period->to])
                ->whereIn('role', ['customer', 'restaurateur'])
                ->selectRaw("{$bucket} AS bucket, role, COUNT(*) AS n")->groupByRaw("{$bucket}, role")->get();
            $points = [];
            foreach (self::buckets($period, $granularity) as $key) {
                $points[$key] = ['bucket' => $key, 'customer' => 0, 'restaurateur' => 0];
            }
            foreach ($rows as $row) {
                if (isset($points[$row->bucket])) {
                    $points[$row->bucket][$row->role] = (int) $row->n;
                }
            }

            return ['granularity' => $granularity, 'points' => array_values($points)];
        });
    }

    /** the keys of every bucket of the period, in order (same format as SqlBuckets::period) */
    public static function buckets(Period $period, string $granularity): array
    {
        $cursor = Carbon::parse($period->from)->startOfDay();
        $end = Carbon::parse($period->to);
        [$cursor, $step, $format] = match ($granularity) {
            'day' => [$cursor, 'addDay', 'Y-m-d'],
            'week' => [$cursor->startOfWeek(Carbon::MONDAY), 'addWeek', 'Y-m-d'],
            'month' => [$cursor->startOfMonth(), 'addMonthNoOverflow', 'Y-m'],
        };
        $keys = [];
        while ($cursor->lessThanOrEqualTo($end)) {
            $keys[] = $cursor->format($format);
            $cursor = $cursor->{$step}();
        }

        return $keys;
    }

    private static function cached(Period $period, string $name, \Closure $compute): array
    {
        return Cache::remember($period->cacheKey($name), (int) config('platform.cache_seconds', 600), $compute);
    }
}
