<?php

namespace App\Http\Controllers\Platform;

use App\Analytics\OrderMetrics;
use App\Analytics\Period;
use App\Analytics\SqlBuckets;
use App\Analytics\Tables;
use App\Http\Controllers\Controller;
use App\Order;
use App\Restaurant;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// tab "Tables" of the platform backoffice: one sub-tab per data set (whitelisted in the route),
// plus the detail pages of an order, a restaurant and a customer
class TableController extends Controller
{
    public const SECTIONS = ['restaurants', 'orders', 'customers', 'zones', 'periods', 'reviews'];

    public function index(Request $request, string $section = 'restaurants'): View
    {
        $period = Period::fromRequest($request);
        $q = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $data = match ($section) {
            'restaurants' => $this->restaurants($request, $period, $q),
            'orders' => $this->orders($request, $period, $q),
            'customers' => $this->customers($request, $period, $q),
            'zones' => $this->zones($request, $period),
            'periods' => $this->periods($request, $period),
            'reviews' => $this->reviews($request, $period, $q),
        };

        return view('platform.tables', ['period' => $period, 'section' => $section, 'sections' => self::SECTIONS, 'q' => $q, ...$data]);
    }

    private function restaurants(Request $request, Period $period, string $q): array
    {
        [$sort, $dir] = Tables::sort($request->query('sort'), $request->query('dir'), Tables::RESTAURANT_SORTS, 'gross_value');

        return ['rows' => Tables::restaurants($period, $q, $sort, $dir), 'sort' => $sort, 'dir' => $dir];
    }

    private function orders(Request $request, Period $period, string $q): array
    {
        [$sort, $dir] = Tables::sort($request->query('sort'), $request->query('dir'), Tables::ORDER_SORTS, 'created_at');
        $state = in_array($request->query('state'), Tables::ORDER_STATES, true) ? $request->query('state') : null;

        return ['rows' => Tables::orders($period, $q, $state, $sort, $dir), 'sort' => $sort, 'dir' => $dir, 'state' => $state];
    }

    private function customers(Request $request, Period $period, string $q): array
    {
        [$sort, $dir] = Tables::sort($request->query('sort'), $request->query('dir'), Tables::CUSTOMER_SORTS, 'gross_value');

        return ['rows' => Tables::customers($period, $q, $sort, $dir), 'sort' => $sort, 'dir' => $dir];
    }

    private function zones(Request $request, Period $period): array
    {
        $by = $request->query('by') === 'postal_code' ? 'postal_code' : 'city';
        [$sort, $dir] = Tables::sort($request->query('sort'), $request->query('dir'), Tables::ZONE_SORTS, 'gross_value');

        return ['rows' => Tables::zones($period, $by, $sort, $dir), 'sort' => $sort, 'dir' => $dir, 'by' => $by];
    }

    private function reviews(Request $request, Period $period, string $q): array
    {
        [$sort, $dir] = Tables::sort($request->query('sort'), $request->query('dir'), Tables::REVIEW_SORTS, 'created_at');
        $stars = (int) $request->query('stars');
        $stars = $stars >= 1 && $stars <= 5 ? $stars : null;
        $visibility = in_array($request->query('visibility'), Tables::REVIEW_VISIBILITY, true) ? $request->query('visibility') : null;
        $withText = $request->boolean('text');

        return [
            'rows' => Tables::reviews($period, $q, $stars, $visibility, $withText, $sort, $dir),
            'sort' => $sort, 'dir' => $dir, 'stars' => $stars, 'visibility' => $visibility, 'withText' => $withText,
        ];
    }

    private function periods(Request $request, Period $period): array
    {
        $unit = in_array($request->query('unit'), Tables::PERIOD_UNITS, true) ? $request->query('unit') : SqlBuckets::granularityFor($period);
        // days only up to a year, so the page stays readable
        if ($unit === 'day' && $period->days() > 366) {
            $unit = 'week';
        }

        return ['rows' => Tables::periods($period, $unit), 'unit' => $unit];
    }

    public function order(Request $request, int $order): View
    {
        $row = DB::table('orders as o')->leftJoin('restaurants as r', 'r.id', '=', 'o.restaurant_id')
            ->where('o.id', $order)->select('o.*', 'r.name as restaurant_name', 'r.city as restaurant_city')->first();
        abort_if($row === null || $row->status === Order::PENDING, 404);

        return view('platform.tables.order', [
            'period' => Period::fromRequest($request),
            'order' => $row,
            'state' => Tables::stateOf($row),
            'lines' => Tables::orderLines($order),
            'account' => User::where('email', $row->customer_email)->first(['id', 'name', 'last_name', 'role']),
        ]);
    }

    public function restaurant(Request $request, int $restaurant): View
    {
        $model = Restaurant::with('user:id,name,last_name,email', 'types:id,name')->findOrFail($restaurant);
        $period = Period::fromRequest($request);
        // the totals of this restaurant only: same definitions, filtered on its id
        $scoped = fn (Period $p) => OrderMetrics::derive(OrderMetrics::orders($p)->where('o.restaurant_id', $model->id)->selectRaw(OrderMetrics::aggregates())->first());
        $unit = SqlBuckets::granularityFor($period);
        $bucket = SqlBuckets::period($unit, 'o.created_at');
        $series = OrderMetrics::orders($period)->where('o.restaurant_id', $model->id)
            ->selectRaw("{$bucket} AS bucket, ".OrderMetrics::aggregates())->groupByRaw($bucket)->get()->keyBy('bucket');

        return view('platform.tables.restaurant', [
            'period' => $period,
            'restaurant' => $model,
            'totals' => $scoped($period),
            'previous' => $scoped($period->previous()),
            'unit' => $unit,
            'series' => array_reverse(array_map(fn ($key) => ['bucket' => $key, ...OrderMetrics::derive($series[$key] ?? null)], OrderMetrics::buckets($period, $unit))),
            'plates' => Tables::topPlates($period, $model->id),
            'rating' => (object) ['avg' => $model->rating_avg, 'n' => $model->rating_count],
        ]);
    }

    public function customer(Request $request): View
    {
        $email = (string) $request->query('email', '');
        abort_unless(filter_var($email, FILTER_VALIDATE_EMAIL), 404);
        $period = Period::fromRequest($request);
        $all = DB::table('orders as o')->where('o.customer_email', $email)->whereIn('o.status', [Order::PAID, Order::FAILED]);
        abort_unless($all->exists(), 404);

        return view('platform.tables.customer', [
            'period' => $period,
            'email' => $email,
            'name' => self::latestName($email),
            'lifetime' => OrderMetrics::derive((clone $all)->selectRaw(OrderMetrics::aggregates())->first()),
            'totals' => OrderMetrics::derive(OrderMetrics::orders($period)->where('o.customer_email', $email)->selectRaw(OrderMetrics::aggregates())->first()),
            'first' => (clone $all)->min('o.created_at'),
            'last' => (clone $all)->max('o.created_at'),
            'orders' => OrderMetrics::orders($period)
                ->where('o.customer_email', $email)
                ->select('o.id', 'o.created_at', 'o.price', 'o.status', 'o.fulfillment_status', 'r.name as restaurant_name', 'o.restaurant_id')
                ->orderByDesc('o.created_at')->paginate(Tables::PER_PAGE)->withQueryString(),
            'account' => User::where('email', $email)->first(['id', 'name', 'last_name', 'role', 'created_at']),
        ]);
    }

    /** the name written on the latest order of an email (guests may use different spellings) */
    private static function latestName(string $email): string
    {
        $last = DB::table('orders')->where('customer_email', $email)->latest('id')->first(['customer_name', 'customer_lastName']);

        return $last ? trim($last->customer_name.' '.$last->customer_lastName) : '';
    }
}
