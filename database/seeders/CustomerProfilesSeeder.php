<?php

namespace Database\Seeders;

use App\Order;
use App\User;
use Database\Seeders\Support\CatalogPlan;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Registered customers (dev/PIANO_AUTH_CLIENTI.md, step 7.3, decision D3):
// A. the most frequent synthetic customers of OrderHistorySeeder get an account, created on the day of
//    their first order, and their orders are linked through orders.user_id;
// B. the demo customer Mario Rossi gets a rich, coherent history (profile in dev/PIANO_DATASET.md):
//    home in Zürich Kreis 4, office in Oerlikon, weekday lunches near the office, dinners at the
//    weekend near home, Japanese and pizza first, seasonal cuisines, vegetarian dishes, regular
//    places and dishes ordered again, a few cancelled orders and failed payments.
// Deterministic (Rng, never ids) and idempotent: existing accounts are kept, Mario is skipped when
// he already has orders, only orders without an account are linked.
class CustomerProfilesSeeder extends Seeder
{
    // Mario's two places (coordinates for the distance to the restaurants; addresses as the checkout stores them)
    public const HOME = ['lat' => 47.3779, 'lng' => 8.5262, 'address' => 'Dienerstrasse 21'];
    public const OFFICE = ['lat' => 47.4112, 'lng' => 8.5446, 'address' => 'Thurgauerstrasse 40'];
    public const PHONE = '079 412 58 33';
    // his eating habit, readable from the dietary_tags of what he orders. Vegetarian and not lactose free:
    // pizzerias and Japanese places almost never declare lactose-free dishes (allergen_info not complete)
    public const HABIT = 'vegetarian';

    // how many restaurants around each place he uses
    private const LUNCH_PLACES = 16;
    private const DINNER_PLACES = 30;

    // cuisines he likes all year, and the ones of each season (months)
    private const FAVOURITE_TYPES = ['Japanese', 'Sushi', 'Ramen', 'Pizzeria', 'Gourmet Pizza'];
    private const SEASONAL_TYPES = [
        'summer' => [[6, 7, 8], ['Poke', 'Mexican', 'Greek', 'Lebanese', 'Vietnamese']],
        'winter' => [[12, 1, 2], ['Indian', 'Thai', 'Chinese', 'Korean']],
        'spring' => [[3, 4, 5], ['Mediterranean', 'Vegetarian', 'Peruvian']],
        'autumn' => [[9, 10, 11], ['Italian Regional', 'Turkish', 'Middle Eastern']],
    ];
    // never for a delivery at home on a weeknight
    private const SPECIAL_SEGMENTS = ['fine_dining', 'starred'];
    // two weeks of holidays every summer: no orders
    private const HOLIDAYS = [['2025-07-28', '2025-08-10'], ['2026-08-03', '2026-08-16']];

    public function run(): void
    {
        $profile = CatalogPlan::profile();
        DB::transaction(function () use ($profile) {
            $this->registerFrequentCustomers((int) ($profile['registered_customers'] ?? 0));
            $this->marioHistory((float) ($profile['mario_scale'] ?? 1.0));
        });
    }

    // ---------- A. synthetic customers with an account ----------

    private function registerFrequentCustomers(int $count): void
    {
        if ($count < 1) {
            return;
        }
        $synthetic = array_column(OrderHistorySeeder::customers(CatalogPlan::profile()['customers']), null, 'email');
        // the most frequent first (email breaks the ties): the same list on every run
        $frequent = DB::table('orders')
            ->whereIn('customer_email', array_keys($synthetic))
            ->selectRaw('customer_email, count(*) AS orders, min(created_at) AS first_order')
            ->groupBy('customer_email')
            ->orderByDesc('orders')
            ->orderBy('customer_email')
            ->limit($count)
            ->get();
        if ($frequent->isEmpty()) {
            return;
        }

        $existing = DB::table('users')->whereIn('email', $frequent->pluck('customer_email'))->pluck('role', 'email');
        // one hash for everyone: bcrypt per account would take seconds
        $password = Hash::make(config('seeding.customer_password', 'foodsy-demo'));
        $rows = [];
        foreach ($frequent as $f) {
            if (isset($existing[$f->customer_email])) {
                continue;
            }
            $person = $synthetic[$f->customer_email];
            // registered just before the first order (the checkout came after the sign-up)
            $created = Carbon::parse($f->first_order)->subMinutes(5);
            $rows[] = [
                'name' => $person['name'],
                'last_name' => $person['last_name'],
                'email' => $f->customer_email,
                'phone' => $person['phone'],
                'password' => $password,
                'role' => User::CUSTOMER,
                'email_verified_at' => $created,
                'created_at' => $created,
                'updated_at' => $created,
            ];
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // their guest orders become account orders; accounts that are not customers are never linked
        $customers = DB::table('users')->where('role', User::CUSTOMER)->whereIn('email', $frequent->pluck('customer_email'))->pluck('email');
        self::linkOrders($customers->all());
    }

    // orders.user_id from the email, only where no account is linked yet (same query on MySQL and SQLite)
    private static function linkOrders(array $emails): void
    {
        foreach (array_chunk($emails, 200) as $chunk) {
            DB::table('orders')->whereNull('user_id')->whereIn('customer_email', $chunk)
                ->update(['user_id' => DB::raw('(SELECT users.id FROM users WHERE users.email = orders.customer_email)')]);
        }
    }

    // ---------- B. Mario Rossi ----------

    private function marioHistory(float $scale): void
    {
        $mario = User::where('email', config('demo.accounts.customer.email'))->first();
        if (! $mario || $mario->role !== User::CUSTOMER || $mario->orders()->exists()) {
            return;
        }
        $places = self::places();
        if (! $places['lunch'] || ! $places['dinner']) {
            throw new \RuntimeException('CustomerProfilesSeeder: no restaurants with a menu, run CatalogRestaurantsSeeder and MenusSeeder first.');
        }
        $menus = self::menus(array_unique([...array_keys($places['lunch']), ...array_keys($places['dinner'])]));
        // how many dishes of his habit each place declares: he goes where he can eat
        foreach ($places as &$list) {
            foreach ($list as $id => &$place) {
                $place['habit'] = count(array_filter($menus[$id]['food'], fn ($p) => $p['habit']));
            }
        }
        unset($list, $place);
        $rng = new Rng('customer-profile:mario');
        // his regular places: the favourite cuisines nearest to the office and to home
        $regulars = [
            'lunch' => self::regulars($places['lunch'], 2),
            'dinner' => self::regulars($places['dinner'], 3),
        ];
        // dishes he already ordered in each restaurant: he often takes them again
        $favourites = [];
        $orders = [];

        $day = Carbon::parse(OrderHistorySeeder::FROM);
        $last = Carbon::parse(OrderHistorySeeder::TO);
        for (; $day->lessThanOrEqualTo($last); $day->addDay()) {
            if (self::onHoliday($day)) {
                continue;
            }
            $weekend = $day->isWeekend() || $day->isFriday();
            // weekday lunch at the office, dinner at home mostly from Friday to Sunday
            $meals = [];
            if (! $day->isWeekend() && $rng->chance(0.24 * $scale)) {
                $meals[] = 'lunch';
            }
            if ($rng->chance(($weekend ? 0.42 : 0.05) * $scale)) {
                $meals[] = 'dinner';
            }
            foreach ($meals as $meal) {
                $restaurantId = $rng->chance(0.45)
                    ? $rng->pick($regulars[$meal])
                    : self::choose($places[$meal], $day, $rng);
                $orders[] = self::order($mario, $meal, $places[$meal][$restaurantId], $menus[$restaurantId], $favourites, $day, $rng);
            }
        }

        OrderHistorySeeder::insert($orders);
        DB::table('orders')->whereNull('user_id')->where('customer_email', $mario->email)
            ->where('created_at', '>=', OrderHistorySeeder::FROM)->update(['user_id' => $mario->id]);

        // the account comes before its first order; the phone completes the checkout prefill
        $first = DB::table('orders')->where('user_id', $mario->id)->min('created_at');
        if ($first && $mario->created_at->greaterThan(Carbon::parse($first))) {
            $mario->created_at = Carbon::parse($first)->subDays(2);
        }
        $mario->phone ??= self::PHONE;
        $mario->save();
    }

    // restaurants around the office (lunch) and home (dinner), nearest first, with their cuisines:
    // in the full dataset they are all in the city of Zürich
    private static function places(): array
    {
        $restaurants = DB::table('restaurants')
            ->whereNotNull('latitude')
            ->whereNotIn('segment', self::SPECIAL_SEGMENTS)
            ->whereExists(fn ($q) => $q->from('plates')->whereColumn('plates.restaurant_id', 'restaurants.id')->whereNull('deleted_at'))
            ->orderBy('name')
            ->get(['id', 'name', 'latitude', 'longitude', 'prep_minutes']);
        $types = DB::table('restaurant_type')->join('types', 'types.id', '=', 'restaurant_type.type_id')
            ->whereIn('restaurant_id', $restaurants->pluck('id'))->get(['restaurant_id', 'types.name'])
            ->groupBy('restaurant_id')->map(fn ($rows) => $rows->pluck('name')->all());

        $near = function (array $place, int $limit) use ($restaurants, $types) {
            return $restaurants
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'prep_minutes' => (int) ($r->prep_minutes ?? 20),
                    'types' => $types[$r->id] ?? [],
                    'km' => self::km($place['lat'], $place['lng'], (float) $r->latitude, (float) $r->longitude),
                ])
                ->sortBy([['km', 'asc'], ['name', 'asc']])
                ->take($limit)
                ->keyBy('id')
                ->all();
        };

        return ['lunch' => $near(self::OFFICE, self::LUNCH_PLACES), 'dinner' => $near(self::HOME, self::DINNER_PLACES)];
    }

    // dishes and drinks of each restaurant, with the dishes of his habit (declared or derived "yes")
    private static function menus(array $restaurantIds): array
    {
        $habit = DB::table('dietary_tag_plate')
            ->join('dietary_tags', 'dietary_tags.id', '=', 'dietary_tag_plate.dietary_tag_id')
            ->where('dietary_tags.code', self::HABIT)->where('dietary_tag_plate.status', 'yes')
            ->pluck('plate_id')->flip();
        $menus = [];
        foreach (DB::table('plates')->whereIn('restaurant_id', $restaurantIds)->whereNull('deleted_at')
            ->orderBy('restaurant_id')->orderBy('name')->get(['id', 'restaurant_id', 'price', 'course']) as $plate) {
            $kind = in_array($plate->course, OrderHistorySeeder::DRINK_COURSES, true) ? 'drinks' : 'food';
            $menus[$plate->restaurant_id][$kind][] = ['id' => $plate->id, 'price' => (float) $plate->price, 'habit' => isset($habit[$plate->id])];
        }
        foreach ($menus as &$menu) {
            $menu['food'] ??= $menu['drinks'];
            $menu['drinks'] ??= [];
        }

        return $menus;
    }

    // the favourite cuisines nearest to a place, where he finds dishes of his habit; fewer conditions when none matches
    private static function regulars(array $places, int $n): array
    {
        $liked = fn ($p) => (bool) array_intersect($p['types'], self::FAVOURITE_TYPES);
        $candidates = array_filter($places, fn ($p) => $liked($p) && $p['habit'] >= 3)
            ?: array_filter($places, $liked)
            ?: $places;

        return array_slice(array_keys($candidates), 0, $n);
    }

    // nearer is better, favourite cuisines three times, the cuisines of the season twice,
    // a place without dishes of his habit rarely
    private static function choose(array $places, Carbon $day, Rng $rng): int
    {
        $season = collect(self::SEASONAL_TYPES)->first(fn ($s) => in_array($day->month, $s[0], true))[1];
        $weights = array_map(function ($p) use ($season) {
            $w = 1 / (0.6 + $p['km']);
            $w *= array_intersect($p['types'], self::FAVOURITE_TYPES) ? 3 : 1;
            $w *= array_intersect($p['types'], $season) ? 2 : 1;
            $w *= $p['habit'] >= 2 ? 1 : 0.1;

            return $w;
        }, $places);

        return (int) $rng->weighted($weights);
    }

    private static function order(User $mario, string $meal, array $place, array $menu, array &$favourites, Carbon $day, Rng $rng): array
    {
        $created = $day->copy()->setTime(...($meal === 'lunch' ? [$rng->int(11, 13), $rng->int(30, 59)] : [$rng->int(18, 21), $rng->int(0, 59)]));
        $known = $favourites[$place['id']] ?? [];

        // one dish at lunch, one to three at dinner (sometimes for two); his habit almost always
        $lines = [];
        $dishes = $meal === 'lunch' ? 1 : $rng->int(1, 3);
        for ($n = 0; $n < $dishes; $n++) {
            $plate = $known && $rng->chance(0.6)
                ? $rng->pick($known)
                : $menu['food'][(int) $rng->weighted(array_map(fn ($p) => $p['habit'] ? 20 : 1, $menu['food']))];
            $lines[$plate['id']] = [($lines[$plate['id']][0] ?? 0) + 1, $plate['price']];
            $favourites[$place['id']][$plate['id']] = $plate;
        }
        if ($menu['drinks'] && $rng->chance($meal === 'lunch' ? 0.3 : 0.6)) {
            $drink = $rng->pick($menu['drinks']);
            $lines[$drink['id']] ??= [1, $drink['price']];
        }
        $total = round(array_sum(array_map(fn ($l) => $l[0] * $l[1], $lines)), 2);

        // only the dinner of the last day is still on its way (earlier ones were delivered long ago)
        $recent = $created->greaterThanOrEqualTo(Carbon::parse(OrderHistorySeeder::TO)->setTime(17, 0));
        $x = $rng->float();
        [$status, $fulfillment] = match (true) {
            $x < 0.03 => [Order::FAILED, null],
            $x < 0.05 => [Order::PAID, Order::CANCELLED],
            $recent => [Order::PAID, Order::PREPARING],
            default => [Order::PAID, Order::DELIVERED],
        };

        return [
            'row' => [
                'restaurant_id' => $place['id'],
                'price' => $total,
                'status' => $status,
                'payment_driver' => 'fake',
                'transaction_id' => $status === Order::PAID ? 'demo_' . substr(hash('sha1', "mario|{$place['name']}|{$created}"), 0, 12) : null,
                'payment_error' => $status === Order::FAILED ? 'Insufficient Funds' : null,
                'fulfillment_status' => $fulfillment,
                'delivered_at' => $fulfillment === Order::DELIVERED ? $created->copy()->addMinutes($place['prep_minutes'] + $rng->int(10, 25)) : null,
                'customer_name' => $mario->name,
                'customer_lastName' => $mario->last_name,
                'customer_email' => $mario->email,
                'customer_phone' => $mario->phone ?? self::PHONE,
                'customer_address' => $meal === 'lunch' ? self::OFFICE['address'] : self::HOME['address'],
                'created_at' => $created,
                'updated_at' => $created,
            ],
            'lines' => $lines,
        ];
    }

    private static function onHoliday(Carbon $day): bool
    {
        foreach (self::HOLIDAYS as [$from, $to]) {
            if ($day->betweenIncluded(Carbon::parse($from), Carbon::parse($to))) {
                return true;
            }
        }

        return false;
    }

    // great-circle distance in km
    private static function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * asin(min(1, sqrt($a)));
    }
}
