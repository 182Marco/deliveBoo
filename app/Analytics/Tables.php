<?php

namespace App\Analytics;

use App\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Queries of the Tables tab (Restaurants, Orders, Customers, Zones, Periods). Everything is
 * paginated or aggregated in SQL; sorting accepts only the keys of the whitelists below and the
 * search text is always a bound parameter.
 */
final class Tables
{
    public const PER_PAGE = 20;

    public const RESTAURANT_SORTS = ['name', 'city', 'gross_value', 'completed_orders', 'average_order', 'cancel_rate', 'rating'];
    public const ORDER_SORTS = ['created_at', 'price'];
    public const ORDER_STATES = ['completed', 'in_progress', 'cancelled', 'failed'];
    public const CUSTOMER_SORTS = ['gross_value', 'completed_orders', 'last_order', 'first_order', 'name'];
    public const ZONE_SORTS = ['zone', 'restaurants', 'gross_value', 'completed_orders', 'average_order', 'revenue'];
    public const PERIOD_UNITS = ['day', 'week', 'month'];

    /** a whitelisted sort key and direction from the request values */
    public static function sort(?string $sort, ?string $dir, array $allowed, string $default, string $defaultDir = 'desc'): array
    {
        $sort = in_array($sort, $allowed, true) ? $sort : $default;
        $dir = in_array($dir, ['asc', 'desc'], true) ? $dir : ($sort === $default ? $defaultDir : 'asc');

        return [$sort, $dir];
    }

    /** "%text%" for LIKE, with the wildcards of the user escaped */
    public static function like(string $text): string
    {
        return '%'.addcslashes($text, '%_\\').'%';
    }

    public static function restaurants(Period $period, string $q, string $sort, string $dir): LengthAwarePaginator
    {
        $sales = OrderMetrics::orders($period)->whereNotNull('o.restaurant_id')
            ->selectRaw('o.restaurant_id, '.OrderMetrics::aggregates())->groupBy('o.restaurant_id');
        $ratings = DB::table('rates')->selectRaw('restaurant_id, AVG(rate) AS avg_rate, COUNT(*) AS rates')->groupBy('restaurant_id');

        $order = match ($sort) {
            'name' => 'LOWER(r.name)',
            'city' => 'LOWER(r.city)',
            'rating' => 'COALESCE(rt.avg_rate, 0)',
            'average_order' => 'CASE WHEN COALESCE(s.completed_orders, 0) > 0 THEN s.completed_value / s.completed_orders ELSE 0 END',
            'cancel_rate' => 'CASE WHEN COALESCE(s.completed_orders, 0) + COALESCE(s.in_progress_orders, 0) + COALESCE(s.cancelled_orders, 0) > 0 THEN s.cancelled_orders * 1.0 / (s.completed_orders + s.in_progress_orders + s.cancelled_orders) ELSE 0 END',
            default => "COALESCE(s.{$sort}, 0)",
        };

        $page = DB::table('restaurants as r')
            ->when($period->city, fn (Builder $b, string $city) => $b->where('r.city', $city))
            ->when($period->typeId, fn (Builder $b, int $type) => $b->whereExists(fn (Builder $e) => $e
                ->from('restaurant_type as t')->whereColumn('t.restaurant_id', 'r.id')->where('t.type_id', $type)))
            ->when($q !== '', fn (Builder $b) => $b->where('r.name', 'like', self::like($q)))
            ->leftJoinSub($sales, 's', 's.restaurant_id', '=', 'r.id')
            ->leftJoinSub($ratings, 'rt', 'rt.restaurant_id', '=', 'r.id')
            ->select('r.id', 'r.name', 'r.slug', 'r.city', 'rt.avg_rate', 'rt.rates', 's.*')
            ->orderByRaw("{$order} {$dir}")->orderBy('r.id')
            ->paginate(self::PER_PAGE)->withQueryString();

        return $page->through(fn ($r) => [
            'id' => (int) $r->id, 'name' => $r->name, 'slug' => $r->slug, 'city' => $r->city,
            'rating' => $r->avg_rate === null ? null : round((float) $r->avg_rate, 2), 'rates' => (int) $r->rates,
            ...OrderMetrics::derive($r),
        ]);
    }

    public static function orders(Period $period, string $q, ?string $state, string $sort, string $dir): LengthAwarePaginator
    {
        return OrderMetrics::orders($period)
            ->when(in_array($state, self::ORDER_STATES, true), fn (Builder $b) => $b->whereRaw(match ($state) {
                'completed' => OrderMetrics::COMPLETED,
                'in_progress' => OrderMetrics::IN_PROGRESS,
                'cancelled' => OrderMetrics::CANCELLED,
                'failed' => OrderMetrics::FAILED,
            }))
            ->when($q !== '', fn (Builder $b) => $b->where(fn (Builder $w) => $w
                ->when(ctype_digit($q), fn (Builder $x) => $x->orWhere('o.id', (int) $q))
                ->orWhere('o.customer_email', 'like', self::like($q))
                ->orWhere('o.customer_name', 'like', self::like($q))
                ->orWhere('o.customer_lastName', 'like', self::like($q))
                ->orWhere('o.transaction_id', 'like', self::like($q))
                ->orWhere('r.name', 'like', self::like($q))))
            ->select('o.id', 'o.created_at', 'o.price', 'o.status', 'o.fulfillment_status', 'o.customer_name', 'o.customer_lastName',
                'o.customer_email', 'o.transaction_id', 'o.restaurant_id', 'r.name as restaurant_name')
            ->orderBy("o.{$sort}", $dir)->orderBy('o.id', $dir)
            ->paginate(self::PER_PAGE)->withQueryString();
    }

    /** customers are identified by email (guests have no account); "registered" if an account has that email */
    public static function customers(Period $period, string $q, string $sort, string $dir): LengthAwarePaginator
    {
        $order = match ($sort) {
            'name' => 'LOWER(MAX(o.customer_lastName))',
            'last_order' => 'MAX(o.created_at)',
            'first_order' => 'MIN(o.created_at)',
            default => $sort,
        };

        $page = OrderMetrics::orders($period)
            ->leftJoin('users as u', 'u.email', '=', 'o.customer_email')
            ->when($q !== '', fn (Builder $b) => $b->where(fn (Builder $w) => $w
                ->orWhere('o.customer_email', 'like', self::like($q))
                ->orWhere('o.customer_name', 'like', self::like($q))
                ->orWhere('o.customer_lastName', 'like', self::like($q))))
            ->selectRaw('o.customer_email AS email, MAX(o.customer_name) AS name, MAX(o.customer_lastName) AS last_name, MIN(o.created_at) AS first_order, MAX(o.created_at) AS last_order, MAX(u.id) AS user_id, '.OrderMetrics::aggregates())
            ->groupBy('o.customer_email')
            ->orderByRaw("{$order} {$dir}")->orderBy('o.customer_email')
            ->paginate(self::PER_PAGE)->withQueryString();

        return $page->through(fn ($c) => [
            'email' => $c->email, 'name' => trim($c->name.' '.$c->last_name),
            'first_order' => $c->first_order, 'last_order' => $c->last_order, 'user_id' => $c->user_id ? (int) $c->user_id : null,
            ...OrderMetrics::derive($c),
        ]);
    }

    public static function zones(Period $period, string $by, string $sort, string $dir): array
    {
        $rows = OrderMetrics::zones($period, $by);
        $total = array_sum(array_column($rows, 'gross_value'));
        $rows = array_map(fn ($z) => [...$z, 'share' => $total > 0 ? $z['gross_value'] / $total : null], $rows);
        usort($rows, fn ($a, $b) => ($dir === 'asc' ? 1 : -1) * ($a[$sort] <=> $b[$sort]));

        return $rows;
    }

    /** one row per day, week or month, newest first, with the change from the row before in time */
    public static function periods(Period $period, string $unit): array
    {
        $points = OrderMetrics::series($period, $unit)['points'];
        $customers = array_column(OrderMetrics::customers($period, $unit)['points'], null, 'bucket');
        $rows = [];
        foreach ($points as $i => $p) {
            $before = $points[$i - 1] ?? null;
            $rows[] = [
                ...$p,
                'customers' => $customers[$p['bucket']]['customers'] ?? 0,
                'new_customers' => $customers[$p['bucket']]['new'] ?? 0,
                'gross_change' => $before && $before['gross_value'] > 0 ? round(($p['gross_value'] - $before['gross_value']) / $before['gross_value'], 4) : null,
            ];
        }

        return array_reverse($rows);
    }

    /** the lines of an order with the price paid (plates deleted later included) */
    public static function orderLines(int $orderId): array
    {
        return DB::table('order_plate as op')
            ->join('plates as p', 'p.id', '=', 'op.plate_id')
            ->where('op.order_id', $orderId)
            ->select('p.name', 'p.course', 'op.quantity', 'op.unit_price')
            ->orderBy('op.id')->get()
            ->map(fn ($l) => ['name' => $l->name, 'course' => $l->course, 'quantity' => (int) $l->quantity, 'unit_price' => (float) $l->unit_price, 'total' => round($l->quantity * $l->unit_price, 2)])
            ->all();
    }

    /** the best selling plates of a restaurant in the period (completed orders) */
    public static function topPlates(Period $period, int $restaurantId, int $limit = 10): array
    {
        return OrderMetrics::orders($period)->whereRaw(OrderMetrics::COMPLETED)->where('o.restaurant_id', $restaurantId)
            ->join('order_plate as op', 'op.order_id', '=', 'o.id')
            ->join('plates as p', 'p.id', '=', 'op.plate_id')
            ->selectRaw('p.id, p.name, SUM(op.quantity) AS quantity, SUM(op.quantity * op.unit_price) AS value')
            ->groupBy('p.id', 'p.name')->orderByDesc('quantity')->limit($limit)->get()
            ->map(fn ($p) => ['name' => $p->name, 'quantity' => (int) $p->quantity, 'value' => round((float) $p->value, 2)])->all();
    }

    public static function stateOf(object $order): string
    {
        return match (true) {
            $order->status === Order::FAILED => 'failed',
            $order->fulfillment_status === Order::DELIVERED => 'completed',
            $order->fulfillment_status === Order::CANCELLED => 'cancelled',
            default => 'in_progress',
        };
    }
}
