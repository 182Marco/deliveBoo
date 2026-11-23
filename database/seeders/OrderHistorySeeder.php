<?php

namespace Database\Seeders;

use App\Order;
use Database\Seeders\Support\Catalog;
use Database\Seeders\Support\CatalogPlan;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Order history of the generated restaurants, January 2025 - November 2026: synthetic customers
// (some regulars, many occasional), lunch and dinner peaks, quantities and the unit price paid.
// Idempotent: a restaurant that already has orders is skipped.
class OrderHistorySeeder extends Seeder
{
    private const FROM = '2025-01-01';
    private const TO = '2026-11-21';

    // average orders per restaurant by segment (full profile)
    private const VOLUME = [
        'fast_food' => 70, 'budget' => 55, 'street_food' => 45, 'ethnic' => 40, 'trattoria' => 35,
        'traditional' => 30, 'premium' => 18, 'fine_dining' => 10, 'starred' => 8,
    ];

    public function run(): void
    {
        $profile = CatalogPlan::profile();
        $plan = CatalogPlan::restaurants();
        $ids = DB::table('restaurants')->whereIn('name', array_column($plan, 'name'))->pluck('id', 'name');
        $withOrders = DB::table('orders')->whereIn('restaurant_id', $ids)->distinct()->pluck('restaurant_id')->flip();
        $customers = self::customers($profile['customers']);
        $customerWeights = array_map(fn ($i) => 1 / (1 + $i / 8), array_keys($customers));
        $from = Carbon::parse(self::FROM);
        $days = $from->diffInDays(Carbon::parse(self::TO));

        foreach (array_chunk($plan, 25) as $batch) {
            DB::transaction(function () use ($batch, $ids, $withOrders, $customers, $customerWeights, $profile, $from, $days) {
                $orders = [];
                foreach ($batch as $r) {
                    $restaurantId = $ids[$r['name']] ?? throw new \RuntimeException('OrderHistorySeeder: run CatalogRestaurantsSeeder first.');
                    if (isset($withOrders[$restaurantId])) {
                        continue;
                    }
                    $menu = DB::table('plates')->where('restaurant_id', $restaurantId)->whereNull('deleted_at')
                        ->get(['id', 'price', 'course'])->all();
                    if (! $menu) {
                        throw new \RuntimeException("OrderHistorySeeder: {$r['name']} has no plates, run MenusSeeder first.");
                    }
                    $food = array_values(array_filter($menu, fn ($p) => ! in_array($p->course, ['water', 'soft_drink', 'beer', 'wine_red', 'wine_white', 'wine_rose', 'sparkling', 'champagne', 'dessert_wine', 'spirit', 'cocktail', 'hot_drink'], true))) ?: $menu;
                    $drinks = array_values(array_diff_key($menu, $food)) ?: $menu;
                    $rng = new Rng("orders:{$r['name']}");
                    // popularity: some places sell three times more than others
                    $count = (int) round(self::VOLUME[$r['segment']] * $rng->between(0.3, 1.8) * $profile['orders_scale']);
                    // a regular customer of this place orders here again and again
                    $regulars = array_map(fn () => $rng->weighted($customerWeights), range(1, 3));
                    for ($n = 0; $n < $count; $n++) {
                        $customer = $customers[$rng->chance(0.35) ? $rng->pick($regulars) : $rng->weighted($customerWeights)];
                        $orders[] = self::order($restaurantId, $r, $customer, $food, $drinks, $rng, $from, $days);
                    }
                }
                self::insert($orders);
            });
        }
    }

    private static function order(int $restaurantId, array $r, array $customer, array $food, array $drinks, Rng $rng, Carbon $from, int $days): array
    {
        // lunch or dinner peaks; bakeries and cafés in the morning
        $hour = in_array($r['cuisine'], ['bakery', 'pastry', 'brunch'], true)
            ? $rng->int(7, 16)
            : ($rng->chance(0.35) ? $rng->int(12, 14) : $rng->int(19, 22));
        $created = $from->copy()->addDays($rng->int(0, $days))->setTime($hour, $rng->int(0, 59), $rng->int(0, 59));

        $lines = [];
        $dishes = $rng->int(1, min(4, count($food)));
        foreach (array_slice($rng->shuffle($food), 0, $dishes) as $plate) {
            $lines[$plate->id] = [$rng->chance(0.75) ? 1 : $rng->int(2, 3), (float) $plate->price];
        }
        if ($rng->chance(0.5)) {
            $drink = $rng->pick($drinks);
            $lines[$drink->id] ??= [$rng->int(1, 2), (float) $drink->price];
        }
        $total = round(array_sum(array_map(fn ($l) => $l[0] * $l[1], $lines)), 2);

        // the last day: orders still on the way; a few historic orders failed or were cancelled
        $recent = $created->greaterThan(Carbon::parse(self::TO)->subDay());
        $x = $rng->float();
        [$status, $fulfillment] = match (true) {
            $x < 0.03 => [Order::FAILED, null],
            $x < 0.05 => [Order::PAID, Order::CANCELLED],
            $recent => [Order::PAID, $rng->pick([Order::RECEIVED, Order::PREPARING, Order::OUT_FOR_DELIVERY])],
            default => [Order::PAID, Order::DELIVERED],
        };

        return [
            'row' => [
                'restaurant_id' => $restaurantId,
                'price' => $total,
                'status' => $status,
                'payment_driver' => 'fake',
                'transaction_id' => $status === Order::PAID ? 'demo_' . substr(hash('sha1', "{$r['name']}|{$created}|{$customer['email']}"), 0, 12) : null,
                'payment_error' => $status === Order::FAILED ? 'Do Not Honor' : null,
                'fulfillment_status' => $fulfillment,
                'delivered_at' => $fulfillment === Order::DELIVERED ? $created->copy()->addMinutes($r['prep_minutes'] + $rng->int(10, 30)) : null,
                'customer_name' => $customer['name'],
                'customer_lastName' => $customer['last_name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'],
                'customer_address' => $customer['address'],
                'created_at' => $created,
                'updated_at' => $created,
            ],
            'lines' => $lines,
        ];
    }

    private static function insert(array $orders): void
    {
        foreach (array_chunk($orders, 500) as $chunk) {
            // seeding runs alone: the rows of one insert get consecutive ids, in order
            $before = (int) DB::table('orders')->max('id');
            DB::table('orders')->insert(array_column($chunk, 'row'));
            $ids = DB::table('orders')->where('id', '>', $before)->orderBy('id')->pluck('id')->all();
            if (count($ids) !== count($chunk)) {
                throw new \RuntimeException('OrderHistorySeeder: unexpected order ids, run it without concurrent writes.');
            }
            $pivot = [];
            foreach ($chunk as $k => $o) {
                foreach ($o['lines'] as $plateId => [$qty, $price]) {
                    $pivot[] = ['order_id' => $ids[$k], 'plate_id' => $plateId, 'quantity' => $qty, 'unit_price' => $price];
                }
            }
            foreach (array_chunk($pivot, 1000) as $p) {
                DB::table('order_plate')->insert($p);
            }
        }
    }

    // synthetic customers: the first ones are the most frequent
    public static function customers(int $n): array
    {
        $people = Catalog::data('people');
        $cities = Catalog::data('cities');
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $rng = new Rng("customer:{$i}");
            $first = $people['first'][($i * 11 + 1) % count($people['first'])];
            $last = $people['last'][($i * 17 + 9) % count($people['last'])];
            $out[] = [
                'name' => $first,
                'last_name' => $last,
                'email' => Catalog::slug($first) . '.' . str_replace('-', '', Catalog::slug($last)) . ($i >= 60 ? $i : '') . '@' . $people['domains'][($i + 1) % 3],
                'phone' => '07' . $rng->pick([6, 7, 8, 9]) . ' ' . $rng->int(100, 999) . ' ' . $rng->int(10, 99) . ' ' . $rng->int(10, 99),
                'address' => $rng->pick($cities['streets']) . ' ' . $rng->int(1, 200),
            ];
        }

        return $out;
    }
}
