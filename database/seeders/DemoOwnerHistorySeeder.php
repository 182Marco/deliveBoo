<?php

namespace Database\Seeders;

use App\Order;
use App\Reviews\RestaurantRating;
use App\User;
use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\CatalogPlan;
use Database\Seeders\Support\ReviewWriter;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// The long, rich history of the demo restaurateur (Federico Romiti, config/demo.php): every restaurant
// he owns gets orders month by month from January 2024 (at least 100 a month in the full profile),
// almost all of them reviewed, so the partner hub shows statistics, reviews and trends, and a future
// AI assistant finds real patterns in his data. Profile and stories in dev/PIANO_DATASET.md.
//
// - customers: a pool for every city of the group, almost all registered (accounts created before
//   their first order); a restaurant wins new customers every month, keeps the loyal ones and loses
//   the others (churn), regulars reorder their favourite dish;
// - time: growth over the years, seasons by cuisine, weekday and holiday peaks, orders only inside
//   the opening hours, prices raised every January (order_plate.unit_price is the price paid);
// - stories (STORIES): a delivery crisis, a slow decline, a price rise that customers notice, a weak
//   dish, a restaurant that improved: the stars, the texts and the volumes all follow them;
// - reviews: about 90% of the delivered orders of registered customers, stars from the latent quality
//   of the place, the delivery time and the dishes; the text starts from the real cause; the owner
//   answers most of them, the last days stay unanswered; a few inappropriate ones for moderation.
//
// Deterministic (Rng keyed by names, never ids) and idempotent: a restaurant whose history exists
// (an order with the TX_PREFIX transaction) is skipped, existing accounts are kept.
class DemoOwnerHistorySeeder extends Seeder
{
    public const TO = '2026-11-23';
    public const TX_PREFIX = 'hub-';

    // orders in a month at the beginning of the history, by segment (full profile, before the floor)
    private const BASE = [
        'fast_food' => 135, 'budget' => 125, 'street_food' => 120, 'ethnic' => 115, 'trattoria' => 112,
        'traditional' => 110, 'premium' => 105, 'fine_dining' => 100, 'starred' => 100,
    ];
    // average stars by segment; every place moves around it (Rng "quality:<name>")
    private const QUALITY = [
        'fast_food' => 3.9, 'budget' => 4.0, 'street_food' => 4.1, 'ethnic' => 4.1, 'trattoria' => 4.2,
        'traditional' => 4.2, 'premium' => 4.4, 'fine_dining' => 4.6, 'starred' => 4.75,
    ];
    // month => factor. Delivery is a winter habit; poke a summer one; Italian comfort food in the cold
    private const SEASONS = [
        'default' => [1 => 1.08, 1.05, 1.0, 0.97, 0.95, 0.93, 0.9, 0.86, 0.98, 1.02, 1.06, 1.12],
        'summer' => [1 => 0.8, 0.84, 0.95, 1.05, 1.2, 1.4, 1.45, 1.3, 1.1, 0.95, 0.85, 0.85],
        'winter' => [1 => 1.18, 1.15, 1.05, 0.95, 0.9, 0.85, 0.82, 0.78, 0.95, 1.05, 1.15, 1.22],
        'festive' => [1 => 0.95, 1.15, 1.0, 0.97, 1.02, 0.95, 0.88, 0.82, 0.98, 1.02, 1.1, 1.55],
    ];
    private const BRAND_SEASON = [
        'Aloha Poke' => 'summer', 'Da Gennarino' => 'winter', 'Trattoria da Nives' => 'winter', 'Atelier Sale e Brace' => 'festive',
    ];
    // Sunday = 0, as opening_hours.weekday
    private const WEEKDAYS = [1.15, 0.85, 0.85, 0.9, 1.0, 1.3, 1.35];
    // prices of the menu are today's: the history paid less (raised every January)
    private const PRICE_YEARS = [2024 => 0.93, 2025 => 0.965];
    private const PAYMENT_ERRORS = ['Do Not Honor', 'Insufficient Funds', 'Expired Card', 'Processor Declined'];

    // What happened to some restaurants (full profile names; missing names are ignored):
    // growth = volume at the end against the start · quality = [from date, average stars] steps
    // late = [from, to, extra minutes min, max] (whole months): deliveries late, more cancellations, customers leave
    // decline = [from, factor at the end] · price_rise = [from, factor] · weak_dish: one dish disappoints
    // topics = [from, topics]: what the unhappy customers talk about from that date
    private const STORIES = [
        'the garage' => ['growth' => 1.75, 'quality' => [['2024-01-01', 3.7], ['2025-03-01', 4.45]]],
        'Burger Station Seefeld' => ['growth' => 1.9, 'quality' => [['2024-01-01', 4.55]]],
        'Burger Station Opfikon Glattbrugg' => ['late' => ['2025-12-01', '2026-02-28', 22, 48]],
        'Da Gennarino Oberwinterthur' => ['decline' => ['2026-01-01', 0.62], 'quality' => [['2024-01-01', 4.25], ['2026-01-01', 3.55]], 'topics' => ['2026-01-01', ['portion', 'food']]],
        'Trattoria da Nives Enge' => ['price_rise' => ['2026-03-01', 1.14], 'decline' => ['2026-03-01', 0.85], 'topics' => ['2026-03-01', ['value']]],
        'Sakura Sushi Express Gfenn' => ['weak_dish' => true],
        'Aloha Poke Uster' => ['growth' => 1.2],
        'Atelier Sale e Brace' => ['growth' => 1.3, 'quality' => [['2024-01-01', 4.7]]],
    ];

    // customers
    private const REGISTERED = 0.94;
    private const LOYAL = 0.2;
    // reviews
    private const REVIEW_CHANCE = 0.9;
    private const COMMENT_CHANCE = ['low' => 0.85, 'mid' => 0.65, 'high' => 0.5];
    private const REPLY_CHANCE = ['low' => 0.95, 'mid' => 0.85, 'high' => 0.6];
    // the last days: fewer reviews written yet, and the owner has not answered them all
    private const FRESH_DAYS = 10;
    private const RETRIES = 12;

    private array $customers = [];
    private array $zones = [];

    public function run(): void
    {
        $profile = CatalogPlan::profile()['owner_history'] ?? null;
        $owner = User::where('email', CatalogPlan::ROMITI)->first();
        if (! $profile || ! $owner) {
            return;
        }
        $restaurants = self::restaurants();
        if (! $restaurants) {
            return;
        }
        $this->zones = self::zones($restaurants, $profile);
        $from = self::ts($profile['from']);
        $to = self::ts(self::TO) + 86399;

        // the group exists from before the first order
        $opening = gmdate('Y-m-d H:i:s', $from - 45 * 86400);
        DB::table('users')->where('id', $owner->id)->where('created_at', '>', $opening)->update(['created_at' => gmdate('Y-m-d H:i:s', $from - 60 * 86400)]);
        DB::table('restaurants')->whereIn('id', array_column($restaurants, 'id'))->where('created_at', '>', $opening)->update(['created_at' => $opening]);

        // pass 1: who orders and when (accounts are created before the first order)
        $first = [];
        foreach ($restaurants as $r) {
            $this->simulate($r, $profile, $from, $to, function (array $o) use (&$first) {
                $first[$o['customer']] = min($first[$o['customer']] ?? PHP_INT_MAX, $o['ts']);
            });
        }
        $userIds = $this->createAccounts($first);

        // pass 2: the same history again, written restaurant by restaurant with its reviews
        $writer = ReviewWriter::fromFiles();
        foreach ($restaurants as $k => $r) {
            if (DB::table('orders')->where('restaurant_id', $r['id'])->where('transaction_id', 'like', self::TX_PREFIX . '%')->exists()) {
                continue;
            }
            DB::transaction(function () use ($r, $k, $profile, $from, $to, $userIds, $writer) {
                $this->write($r, $k, $profile, $from, $to, $userIds, $writer);
            });
        }
    }

    /** emails of the whole customer pool of the profile (UserRolesTest) */
    public static function customerEmails(): array
    {
        $profile = CatalogPlan::profile()['owner_history'] ?? null;
        $restaurants = $profile ? self::restaurants() : [];
        if (! $restaurants) {
            return [];
        }
        $seeder = new self();
        $seeder->zones = self::zones($restaurants, $profile);
        $emails = [];
        foreach ($seeder->zones as $city => $size) {
            for ($i = 0; $i < $size; $i++) {
                $emails[] = $seeder->customer("{$city}#{$i}")['email'];
            }
        }

        return $emails;
    }

    // ---------------------------------------------------------------- data of the group

    // restaurants of the demo owner by name, with what the history needs
    private static function restaurants(): array
    {
        $rows = DB::table('restaurants as r')->join('users as u', 'u.id', '=', 'r.user_id')
            ->where('u.email', CatalogPlan::ROMITI)->orderBy('r.name')
            ->get(['r.id', 'r.name', 'r.segment', 'r.city', 'r.prep_minutes']);
        $out = [];
        foreach ($rows as $row) {
            $menu = DB::table('plates')->where('restaurant_id', $row->id)->whereNull('deleted_at')->orderBy('name')
                ->get(['id', 'name', 'price', 'course', 'available_from_month as from', 'available_to_month as to'])->all();
            if (! $menu) {
                continue;
            }
            $hours = [];
            foreach (DB::table('opening_hours')->where('restaurant_id', $row->id)->orderBy('weekday')->orderBy('opens_at')->get() as $h) {
                $hours[(int) $h->weekday][] = [self::minutes($h->opens_at), self::minutes($h->closes_at)];
            }
            $out[] = [
                'id' => (int) $row->id,
                'name' => $row->name,
                'segment' => $row->segment ?: 'budget',
                'city' => $row->city ?: 'Zürich',
                'prep' => (int) ($row->prep_minutes ?: 20),
                'menu' => $menu,
                // no opening hours stored: open every day 11-23
                'hours' => $hours ?: array_fill(0, 7, [[660, 1380]]),
            ];
        }

        return $out;
    }

    // customers of every city: a pool sized on the restaurants of the group there
    private static function zones(array $restaurants, array $profile): array
    {
        $zones = [];
        foreach ($restaurants as $r) {
            $zones[$r['city']] = ($zones[$r['city']] ?? 0) + $profile['pool_per_restaurant'];
        }
        ksort($zones);

        return $zones;
    }

    private function customer(string $key): array
    {
        if (isset($this->customers[$key])) {
            return $this->customers[$key];
        }
        [$city, $i] = explode('#', $key);
        $people = Catalog::data('people');
        $rng = new Rng("hub-customer:{$key}");
        $first = $rng->pick($people['first']);
        $last = $rng->pick($people['last']);
        $loyal = $rng->chance(self::LOYAL);

        return $this->customers[$key] = [
            'name' => $first,
            'last_name' => $last,
            'email' => Catalog::slug($first) . '.' . str_replace('-', '', Catalog::slug($last)) . '.' . str_replace('-', '', Catalog::slug($city)) . $i . '@' . $rng->pick($people['domains']),
            'phone' => '07' . $rng->pick([6, 7, 8, 9]) . ' ' . $rng->int(100, 999) . ' ' . $rng->int(10, 99) . ' ' . $rng->int(10, 99),
            'address' => $rng->pick(Catalog::data('cities')['streets']) . ' ' . $rng->int(1, 160),
            'registered' => $rng->chance(self::REGISTERED),
            // how often this person orders (a few order every week) and how long they stay
            'weight' => $loyal ? $rng->between(3, 8) : 1 / max(0.08, $rng->float() ** 0.8),
            'retention' => $loyal ? 0.97 : $rng->between(0.72, 0.9),
        ];
    }

    // ---------------------------------------------------------------- accounts

    /** @return array<string, int> email => user id of the registered customers */
    private function createAccounts(array $first): array
    {
        $password = Hash::make(config('seeding.customer_password', 'foodsy-demo'));
        $emails = [];
        $rows = [];
        foreach ($first as $key => $ts) {
            $c = $this->customer($key);
            if (! $c['registered']) {
                continue;
            }
            $emails[] = $c['email'];
            // signed up a little before the first checkout
            $created = gmdate('Y-m-d H:i:s', $ts - (new Rng("hub-signup:{$c['email']}"))->int(5 * 60, 3 * 86400));
            $rows[$c['email']] = [
                'name' => $c['name'],
                'last_name' => $c['last_name'],
                'email' => $c['email'],
                'phone' => $c['phone'],
                'password' => $password,
                'role' => User::CUSTOMER,
                'email_verified_at' => $created,
                'created_at' => $created,
                'updated_at' => $created,
            ];
        }
        $ids = [];
        foreach (array_chunk($emails, 1000) as $chunk) {
            $ids += DB::table('users')->whereIn('email', $chunk)->pluck('id', 'email')->all();
        }
        $missing = array_values(array_diff_key($rows, $ids));
        foreach (array_chunk($missing, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }
        foreach (array_chunk(array_column($missing, 'email'), 1000) as $chunk) {
            $ids += DB::table('users')->whereIn('email', $chunk)->pluck('id', 'email')->all();
        }

        return $ids;
    }

    // ---------------------------------------------------------------- the history of one restaurant

    /**
     * Calls $emit for every order of the restaurant, oldest month first. Pure: the same restaurant
     * always gives the same orders (pass 1 and pass 2 must agree).
     */
    private function simulate(array $r, array $profile, int $from, int $to, callable $emit): void
    {
        $rng = new Rng("hub:{$r['name']}");
        $story = self::STORIES[$r['name']] ?? [];
        $season = self::SEASONS[self::BRAND_SEASON[self::brand($r['name'])] ?? 'default'];
        $fine = in_array($r['segment'], ['premium', 'fine_dining', 'starred'], true);
        $base = (self::BASE[$r['segment']] ?? 110) * $profile['scale'] * $rng->between(0.9, 1.15);
        $growth = $story['growth'] ?? $rng->between(1.25, 1.6);

        [$food, $drinks, $popularity, $star, $weak] = self::menuPlan($r, $story);
        $zone = $this->zones[$r['city']];

        $seen = [];      // customer key => favourite dish (index in the menu)
        $active = [];    // customer key => weight
        $monthIndex = 0;
        [$y, $m] = [(int) gmdate('Y', $from), (int) gmdate('n', $from)];
        for ($start = gmmktime(0, 0, 0, $m, 1, $y); $start <= $to; $start = gmmktime(0, 0, 0, $m, 1, $y)) {
            $daysInMonth = (int) gmdate('t', $start);
            $days = min($daysInMonth, intdiv($to - $start, 86400) + 1);
            $t = ($start - $from) / max(1, $to - $from);

            $late = self::inPeriod($story['late'] ?? null, $start);
            $volume = $base * (1 + ($growth - 1) * $t) * $season[$m] * $rng->between(0.93, 1.07)
                * self::declineFactor($story['decline'] ?? null, $start, $to)
                * ($late ? 0.82 : 1.0);
            $floor = $profile['min_monthly'] + $rng->int(0, (int) ceil($profile['min_monthly'] * 0.12));
            $count = (int) round(max($floor, $volume) * $days / $daysInMonth);

            // customers leave (more of them during a crisis), the others stay active
            foreach ($active as $key => $w) {
                if (! $rng->chance($this->customer($key)['retention'] - ($late ? 0.12 : 0))) {
                    unset($active[$key]);
                }
            }
            $activeKeys = array_keys($active);
            $activeCum = self::cumulative(array_values($active));
            $returning = min(0.75, 0.2 + 0.05 * $monthIndex);

            // dishes of the season
            $available = array_values(array_filter($food, fn ($i) => self::inSeason($r['menu'][$i], $m))) ?: $food;
            $availableCum = self::cumulative(array_map(fn ($i) => $popularity[$i], $available));
            $dayCum = self::cumulative(array_map(fn ($d) => self::dayWeight($r, $y, $m, $d, $fine), range(1, $days)));
            if ($dayCum[count($dayCum) - 1] <= 0) {
                $dayCum = range(1, $days);
            }

            $orders = [];
            for ($n = 0; $n < $count; $n++) {
                // the customer: a returning one, or somebody new for this place
                if ($activeKeys && $rng->chance($returning)) {
                    $key = $activeKeys[self::draw($rng, $activeCum)];
                } else {
                    $key = null;
                    for ($try = 0; $try < 25 && $key === null; $try++) {
                        $candidate = "{$r['city']}#" . $rng->int(0, $zone - 1);
                        $key = isset($seen[$candidate]) ? null : $candidate;
                    }
                    $key ??= "{$r['city']}#" . $rng->int(0, $zone - 1);
                    if (! isset($seen[$key])) {
                        $seen[$key] = $available[self::draw($rng, $availableCum)];
                        $active[$key] = $this->customer($key)['weight'];
                    }
                }

                // when: a day of the month and a time inside the opening hours
                $day = self::draw($rng, $dayCum) + 1;
                $dayStart = gmmktime(0, 0, 0, $m, $day, $y);
                $minute = self::orderMinute($rng, $r['hours'][(int) gmdate('w', $dayStart)] ?? [[660, 1380]]);
                $ts = $dayStart + $minute * 60 + $rng->int(0, 59);
                if ($ts > $to) {
                    $ts = $to - $rng->int(600, 7200);
                }

                // what: one to four dishes (the favourite one again, often), sometimes drinks
                $lines = [];
                $dishes = $fine ? $rng->int(2, 4) : (int) self::draw($rng, [0.45, 0.8, 0.95, 1.0]) + 1;
                if (isset($seen[$key]) && in_array($seen[$key], $available, true) && $rng->chance(0.55)) {
                    $lines[$seen[$key]] = $fine ? 2 : ($rng->chance(0.8) ? 1 : 2);
                }
                for ($try = 0; count($lines) < $dishes && $try < 12; $try++) {
                    $i = $available[self::draw($rng, $availableCum)];
                    $lines[$i] ??= $fine && $rng->chance(0.6) ? 2 : ($rng->chance(0.78) ? 1 : $rng->int(2, 3));
                }
                if ($drinks && $rng->chance($fine ? 0.8 : 0.45)) {
                    $i = $rng->pick($drinks);
                    $lines[$i] ??= $rng->int(1, 2);
                }

                // delivery: preparation and ride, longer on busy evenings and during a crisis
                $busy = in_array((int) gmdate('w', $ts), [5, 6], true) && $minute >= 1110 && $minute <= 1230;
                $ride = $rng->int(10, 26) + ($busy ? $rng->int(0, 10) : 0) + ($late ? $rng->int($story['late'][2], $story['late'][3]) : 0);
                $x = $rng->float();
                [$status, $fulfillment] = match (true) {
                    $x < 0.025 => [Order::FAILED, null],
                    $x < ($late ? 0.09 : 0.04) => [Order::PAID, Order::CANCELLED],
                    $ts > $to - 4 * 3600 => [Order::PAID, $rng->pick([Order::RECEIVED, Order::PREPARING, Order::OUT_FOR_DELIVERY])],
                    default => [Order::PAID, Order::DELIVERED],
                };
                $orders[] = [
                    'ts' => $ts,
                    'customer' => $key,
                    'lines' => $lines,
                    'status' => $status,
                    'fulfillment' => $fulfillment,
                    'minutes' => $r['prep'] + $ride,
                    'late' => $ride - 26,
                    'error' => $status === Order::FAILED ? $rng->pick(self::PAYMENT_ERRORS) : null,
                ];
            }
            usort($orders, fn ($a, $b) => $a['ts'] <=> $b['ts']);
            foreach ($orders as $o) {
                $emit($o + ['star' => $star, 'weak' => $weak]);
            }
            $monthIndex++;
            [$y, $m] = $m === 12 ? [$y + 1, 1] : [$y, $m + 1];
        }
    }

    // food and drinks of the menu (indexes), popularity of every dish, the best seller and the weak dish
    private static function menuPlan(array $r, array $story): array
    {
        $food = [];
        $drinks = [];
        foreach ($r['menu'] as $i => $p) {
            if (in_array($p->course, OrderHistorySeeder::DRINK_COURSES, true)) {
                $drinks[] = $i;
            } else {
                $food[] = $i;
            }
        }
        $food = $food ?: array_keys($r['menu']);
        $rng = new Rng("hub-popularity:{$r['name']}");
        $popularity = [];
        foreach ($food as $i) {
            $popularity[$i] = 0.15 + $rng->float() ** 2.2;
        }
        arsort($popularity);
        $ranked = array_keys($popularity);
        $star = $ranked[0];
        $popularity[$star] *= 2.5;
        // the weak dish sells well (it is on the photos) but disappoints
        $weak = ! empty($story['weak_dish']) && count($ranked) > 3 ? $ranked[2] : null;

        return [$food, $drinks, $popularity, $star, $weak];
    }

    // ---------------------------------------------------------------- writing orders and reviews

    private function write(array $r, int $k, array $profile, int $from, int $to, array $userIds, ReviewWriter $writer): void
    {
        $orders = [];
        $meta = [];
        $this->simulate($r, $profile, $from, $to, function (array $o) use (&$orders, &$meta, $r, $userIds) {
            $c = $this->customer($o['customer']);
            $created = gmdate('Y-m-d H:i:s', $o['ts']);
            $lines = [];
            $total = 0.0;
            foreach ($o['lines'] as $i => $qty) {
                $p = $r['menu'][$i];
                $price = self::pricePaid((float) $p->price, $o['ts'], self::STORIES[$r['name']]['price_rise'] ?? null);
                $lines[$p->id] = [$qty, $price];
                $total += $qty * $price;
            }
            $delivered = $o['fulfillment'] === Order::DELIVERED ? gmdate('Y-m-d H:i:s', $o['ts'] + $o['minutes'] * 60) : null;
            $orders[] = [
                'row' => [
                    'restaurant_id' => $r['id'],
                    'user_id' => $c['registered'] ? ($userIds[$c['email']] ?? null) : null,
                    'price' => round($total, 2),
                    'status' => $o['status'],
                    'payment_driver' => 'fake',
                    'transaction_id' => $o['status'] === Order::PAID ? self::TX_PREFIX . substr(hash('sha1', "{$r['name']}|{$created}|{$c['email']}"), 0, 14) : null,
                    'payment_error' => $o['error'],
                    'fulfillment_status' => $o['fulfillment'],
                    'delivered_at' => $delivered,
                    'customer_name' => $c['name'],
                    'customer_lastName' => $c['last_name'],
                    'customer_email' => $c['email'],
                    'customer_phone' => $c['phone'],
                    'customer_address' => $c['address'],
                    'created_at' => $created,
                    'updated_at' => $delivered ?? $created,
                ],
                'lines' => $lines,
            ];
            $meta[] = $o;
        });
        $ids = OrderHistorySeeder::insert($orders);

        // reviews
        $rows = [];
        $used = [];
        foreach ($orders as $n => $order) {
            $row = $order['row'];
            if ($row['user_id'] === null || $row['delivered_at'] === null) {
                continue;
            }
            $review = $this->review($r, $row, $meta[$n], $to, $writer, $used);
            if ($review) {
                $rows[] = ['order_id' => $ids[$n]] + $review;
            }
        }
        $rows = $this->flag($r, $k, $rows, $to, $writer);
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('reviews')->insert($chunk);
        }
        RestaurantRating::refresh($r['id']);
    }

    private function review(array $r, array $row, array $o, int $to, ReviewWriter $writer, array &$used): ?array
    {
        $key = "hub-review:{$row['customer_email']}|{$r['name']}|{$row['created_at']}";
        $rng = new Rng($key);
        $deliveredTs = self::ts($row['delivered_at']);
        $fresh = $deliveredTs > $to - self::FRESH_DAYS * 86400;
        if (! $rng->chance($fresh ? 0.55 : self::REVIEW_CHANCE)) {
            return null;
        }
        $story = self::STORIES[$r['name']] ?? [];
        $dishes = array_keys($o['lines']);
        $food = array_values(array_filter($dishes, fn ($i) => ! in_array($r['menu'][$i]->course, OrderHistorySeeder::DRINK_COURSES, true)));
        $names = array_map(fn ($i) => $r['menu'][$i]->name, $food);
        $hasWeak = $o['weak'] !== null && in_array($o['weak'], $dishes, true);
        $hasStar = in_array($o['star'], $dishes, true);

        // stars: the place on that day, then what happened with this order
        $quality = self::quality($r, $story, $o['ts']) + $rng->between(-1.1, 0.9);
        $quality -= $o['late'] > 30 ? 2.0 : ($o['late'] > 15 ? 1.0 : 0.0);
        $quality -= $hasWeak && $rng->chance(0.75) ? 1.4 : 0.0;
        $quality += $hasStar ? 0.3 : 0.0;
        $rating = $rng->chance(0.03) ? $rng->int(1, 2) : max(1, min(5, (int) round($quality)));
        $band = ReviewWriter::band($rating);

        $comment = null;
        if ($rng->chance(self::COMMENT_CHANCE[$band])) {
            // the text starts from the real cause of the stars
            $prefer = [];
            $plates = $names;
            if ($band !== 'high' && $o['late'] > 15) {
                $prefer = ['delivery', 'temperature'];
            } elseif ($band !== 'high' && $hasWeak) {
                $prefer = ['plate'];
                $plates = [$r['menu'][$o['weak']]->name];
            } elseif ($band !== 'high' && isset($story['topics']) && $o['ts'] >= self::ts($story['topics'][0])) {
                $prefer = $story['topics'][1];
            } elseif ($band === 'high' && $hasStar && $rng->chance(0.5)) {
                $prefer = ['plate'];
                $plates = [$r['menu'][$o['star']]->name];
            }
            $comment = $writer->comment($rng, $rating, $plates, $prefer);
            for ($try = 0; $try < self::RETRIES && isset($used[$comment]); $try++) {
                $comment = $writer->comment($rng, $rating, $plates, $prefer);
            }
            $used[$comment] = true;
        }

        $created = min($to - 300, $deliveredTs + $rng->int(40 * 60, 48 * 3600));
        $reply = null;
        $repliedAt = null;
        if ($comment !== null && $rng->chance(self::REPLY_CHANCE[$band] * ($fresh ? 0.3 : 1))) {
            $reply = $writer->reply($rng, $rating, $row['customer_name']);
            $repliedAt = gmdate('Y-m-d H:i:s', min($to - 60, $created + $rng->int(30 * 60, 40 * 3600)));
        }

        return [
            'restaurant_id' => $r['id'],
            'user_id' => $row['user_id'],
            'rating' => $rating,
            'comment' => $comment,
            'is_current' => true,
            'reply' => $reply,
            'replied_at' => $repliedAt,
            'hidden_at' => null,
            'created_at' => gmdate('Y-m-d H:i:s', $created),
            'updated_at' => $repliedAt ?? gmdate('Y-m-d H:i:s', $created),
            'key' => $key,
        ];
    }

    // every fifth restaurant gets one recent inappropriate review (moderation demo), categories in turn
    private function flag(array $r, int $k, array $rows, int $to, ReviewWriter $writer): array
    {
        $pick = null;
        if ($k % 5 === 0) {
            $recent = self::ts(gmdate('Y-m-d', $to - 90 * 86400));
            foreach ($rows as $i => $row) {
                if ($row['comment'] !== null && self::ts(substr($row['created_at'], 0, 10)) >= $recent
                    && ($pick === null || hash('sha1', 'hub-flag:' . $row['key']) < hash('sha1', 'hub-flag:' . $rows[$pick]['key']))) {
                    $pick = $i;
                }
            }
        }
        if ($pick !== null) {
            $slot = intdiv($k, 5);
            $flagged = $writer->flaggedTexts();
            $category = ReviewWriter::FLAG_CATEGORIES[$slot % count(ReviewWriter::FLAG_CATEGORIES)];
            // from the end of the list: ReviewsSeeder starts from the other side
            $texts = array_values($flagged[$category]);
            $rows[$pick]['comment'] = $texts[max(0, count($texts) - 1 - intdiv($slot, count(ReviewWriter::FLAG_CATEGORIES)))];
            $rows[$pick]['reply'] = null;
            $rows[$pick]['replied_at'] = null;
            $rows[$pick]['updated_at'] = $rows[$pick]['created_at'];
        }

        return array_map(function ($row) {
            unset($row['key']);

            return $row;
        }, $rows);
    }

    // ---------------------------------------------------------------- helpers

    private static function quality(array $r, array $story, int $ts): float
    {
        $value = null;
        foreach ($story['quality'] ?? [] as [$date, $q]) {
            if ($ts >= self::ts($date)) {
                $value = $q;
            }
        }

        return $value ?? max(3.4, min(4.85, (self::QUALITY[$r['segment']] ?? 4.0) + (new Rng("quality:{$r['name']}"))->between(-0.35, 0.4)));
    }

    private static function pricePaid(float $price, int $ts, ?array $rise): float
    {
        $factor = self::PRICE_YEARS[(int) gmdate('Y', $ts)] ?? 1.0;
        if ($rise && $ts < self::ts($rise[0])) {
            $factor /= $rise[1];
        }

        return $factor === 1.0 ? $price : max(0.5, round($price * $factor * 10) / 10);
    }

    private static function dayWeight(array $r, int $y, int $m, int $d, bool $fine): float
    {
        $weekday = (int) gmdate('w', gmmktime(0, 0, 0, $m, $d, $y));
        if (empty($r['hours'][$weekday])) {
            return 0.0;
        }
        $w = self::WEEKDAYS[$weekday];
        $special = match (sprintf('%02d-%02d', $m, $d)) {
            '12-24' => $fine ? 3.0 : 0.6,
            '12-25' => $fine ? 1.5 : 0.7,
            '12-31' => $fine ? 3.5 : 1.4,
            '01-01' => 1.5,
            '02-14' => $fine ? 3.0 : 1.3,
            default => 1.0,
        };

        return $w * $special;
    }

    // a minute of the day inside the opening hours: lunch and dinner peaks, a quiet afternoon
    private static function orderMinute(Rng $rng, array $windows): int
    {
        $slots = [];
        $weights = [];
        foreach ($windows as [$open, $close]) {
            $close = $close <= $open ? 1440 : $close;
            foreach ([[690, 840, 0.32], [840, 1080, 0.07], [1080, 1335, 0.61]] as [$a, $b, $w]) {
                $lo = max($open + 10, $a);
                $hi = min($close - 20, $b);
                if ($hi - $lo >= 15) {
                    $slots[] = [$lo, $hi];
                    $weights[] = $w;
                }
            }
        }
        if (! $slots) {
            [$open, $close] = $windows[0];

            return $open + intdiv(max(0, $close - $open), 2);
        }
        [$lo, $hi] = $slots[self::draw($rng, self::cumulative($weights))];

        // two draws: busier in the middle of the slot
        return $lo + (int) round(($hi - $lo) * ($rng->float() + $rng->float()) / 2);
    }

    private static function declineFactor(?array $decline, int $ts, int $to): float
    {
        if (! $decline || $ts < self::ts($decline[0])) {
            return 1.0;
        }
        $t = ($ts - self::ts($decline[0])) / max(1, $to - self::ts($decline[0]));

        return 1 + ($decline[1] - 1) * $t;
    }

    private static function inPeriod(?array $period, int $ts): bool
    {
        return $period !== null && $ts >= self::ts($period[0]) && $ts <= self::ts($period[1]);
    }

    private static function inSeason(object $plate, int $month): bool
    {
        [$a, $b] = [$plate->from, $plate->to];
        if ($a === null || $b === null) {
            return true;
        }

        return $a <= $b ? $month >= $a && $month <= $b : $month >= $a || $month <= $b;
    }

    private static function brand(string $name): string
    {
        foreach (array_keys(CatalogPlan::ROMITI_BRANDS) as $brand) {
            if (str_starts_with($name, $brand)) {
                return $brand;
            }
        }

        return $name;
    }

    private static function cumulative(array $weights): array
    {
        $sum = 0.0;
        $out = [];
        foreach ($weights as $w) {
            $out[] = $sum += $w;
        }

        return $out;
    }

    // index of a draw on cumulative weights (binary search)
    private static function draw(Rng $rng, array $cumulative): int
    {
        $x = $rng->float() * $cumulative[count($cumulative) - 1];
        [$lo, $hi] = [0, count($cumulative) - 1];
        while ($lo < $hi) {
            $mid = intdiv($lo + $hi, 2);
            if ($cumulative[$mid] > $x) {
                $hi = $mid;
            } else {
                $lo = $mid + 1;
            }
        }

        return $lo;
    }

    private static function minutes(string $time): int
    {
        [$h, $m] = explode(':', $time);

        return (int) $h * 60 + (int) $m;
    }

    private static function ts(string $date): int
    {
        return strtotime($date . (strlen($date) === 10 ? ' 00:00:00' : '') . ' UTC');
    }
}
