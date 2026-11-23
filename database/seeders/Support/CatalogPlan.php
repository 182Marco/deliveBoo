<?php

namespace Database\Seeders\Support;

// The generated owners and restaurants, computed in memory from the data files only
// (never from ids or from the database), so every run plans exactly the same catalog.
// A concept (brand) belongs to one owner; chains have branches named after the neighbourhood.
final class CatalogPlan
{
    public const ROMITI = 'federico.romiti@foodsy.test';

    // the restaurant group of the demo account: brand => branches (30 in all, full profile)
    public const ROMITI_BRANDS = [
        'Burger Station' => 8, 'Da Gennarino' => 6, 'Aloha Poke' => 6,
        'Trattoria da Nives' => 5, 'Sakura Sushi Express' => 4, 'Atelier Sale e Brace' => 1,
    ];

    // big chains are casual formats; groups of a few places can also run trattorias
    private const BIG_CHAIN = ['budget', 'fast_food', 'street_food'];
    private const CHAIN = ['budget', 'fast_food', 'street_food', 'ethnic'];
    private const SINGLE_ONLY = ['premium', 'fine_dining', 'starred'];

    private static array $cache = [];

    public static function profile(): array
    {
        $name = config('seeding.profile', 'full');

        return config("seeding.profiles.{$name}") ?? throw new \RuntimeException("Unknown seeding profile {$name}.");
    }

    /** @return array<int, array> owners with their planned restaurants */
    public static function owners(): array
    {
        $key = config('seeding.profile', 'full');
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        $profile = self::profile();
        $concepts = Catalog::concepts();
        $usedNames = array_flip(self::legacyRestaurantNames());

        $owners = [];
        for ($i = 0; $i < $profile['new_owners']; $i++) {
            $owners[] = self::person($i);
        }

        // ---------------------------------------------------------------- brands of every owner
        if (isset($profile['concepts'])) {
            foreach ($profile['concepts'] as $i => $names) {
                $owners[$i]['brands'] = array_fill_keys($names, 1);
            }
        } else {
            $pool = (new Rng('concept-pool'))->shuffle(array_keys(array_diff_key($concepts, self::ROMITI_BRANDS)));
            $counts = self::restaurantCounts(count($owners));
            $order = array_keys($counts);
            usort($order, fn ($a, $b) => $counts[$b] <=> $counts[$a]);
            foreach ($order as $i) {
                $rng = new Rng("brands:{$i}");
                $n = $counts[$i];
                $wanted = match (true) {
                    $n >= 20 => 2, $n >= 9 => $rng->int(2, 3), $n >= 3 => $rng->int(1, min(3, $n)), $n === 2 => $rng->int(1, 2), default => 1,
                };
                $brands = [];
                foreach ($pool as $k => $concept) {
                    if (count($brands) === $wanted) {
                        break;
                    }
                    $segment = $concepts[$concept]['segment'];
                    $single = in_array($segment, self::SINGLE_ONLY, true);
                    // a chain grows only with casual concepts; a one-off premium place stays one
                    if (($n >= 20 && ! in_array($segment, self::BIG_CHAIN, true))
                        || ($n >= 9 && ! in_array($segment, self::CHAIN, true))
                        || ($single && $n - array_sum($brands) < 1)) {
                        continue;
                    }
                    $brands[$concept] = 1;
                    unset($pool[$k]);
                }
                // the branches left go to the chainable brands, the first one first
                $chainable = array_values(array_filter(array_keys($brands), fn ($c) => ! in_array($concepts[$c]['segment'], self::SINGLE_ONLY, true)));
                for ($left = $n - count($brands), $j = 0; $left > 0 && $chainable; $left--, $j++) {
                    $brands[$chainable[$j % max(1, min(count($chainable), $j < $n / 2 ? 1 : 2))]]++;
                }
                $owners[$i]['brands'] = $brands;
            }
            // every concept is used: the leftovers become one more restaurant of the small owners
            $small = array_values(array_filter(array_keys($owners), fn ($i) => array_sum($owners[$i]['brands']) <= 4));
            foreach (array_values($pool) as $k => $concept) {
                $owners[$small[$k % count($small)]]['brands'][$concept] = 1;
            }
        }

        // ---------------------------------------------------------------- restaurants of every brand
        foreach ($owners as $i => $owner) {
            $owners[$i]['restaurants'] = [];
            foreach ($owner['brands'] as $concept => $branches) {
                array_push($owners[$i]['restaurants'], ...self::branches($concepts[$concept], $branches, $usedNames));
            }
        }

        // the demo account gets its own group (its historic restaurants are untouched)
        $romiti = ['email' => self::ROMITI, 'existing' => true, 'restaurants' => []];
        $quota = $profile['romiti_new'];
        foreach (self::ROMITI_BRANDS as $concept => $branches) {
            $take = min($branches, $quota);
            if ($take <= 0) {
                break;
            }
            array_push($romiti['restaurants'], ...self::branches($concepts[$concept], $take, $usedNames));
            $quota -= $take;
        }
        $owners[] = $romiti;

        return self::$cache[$key] = $owners;
    }

    /** @return array<int, array> every planned restaurant with its owner email */
    public static function restaurants(): array
    {
        $all = [];
        foreach (self::owners() as $owner) {
            foreach ($owner['restaurants'] as $r) {
                $all[] = [...$r, 'owner' => $owner['email']];
            }
        }

        return $all;
    }

    // ---------------------------------------------------------------- people

    private static function person(int $i): array
    {
        $people = Catalog::data('people');
        $rng = new Rng("owner:{$i}");
        $first = $people['first'][($i * 7 + 3) % count($people['first'])];
        $last = $people['last'][($i * 13 + 5) % count($people['last'])];
        $email = Catalog::slug($first) . '.' . str_replace('-', '', Catalog::slug($last)) . '@' . $people['domains'][$i % 3];

        return [
            'email' => $email,
            'name' => $first,
            'last_name' => $last,
            'company_name' => str_replace('{last}', $last, $rng->pick($people['company'])),
            'phone' => '07' . $rng->pick([6, 7, 8, 9]) . ' ' . $rng->int(100, 999) . ' ' . $rng->int(10, 99) . ' ' . $rng->int(10, 99),
            'vat_number' => self::vatNumber($i),
            'existing' => false,
        ];
    }

    // valid Italian VAT number (11 digits, Luhn-like check digit), never starting with 0
    public static function vatNumber(int $i): string
    {
        $body = (string) (1000000000 + (($i + 1) * 7919 * 104729) % 8999999999);
        $sum = 0;
        foreach (str_split($body) as $pos => $d) {
            $d = (int) $d;
            $sum += $pos % 2 === 0 ? $d : ($d * 2 > 9 ? $d * 2 - 9 : $d * 2);
        }

        return $body . ((10 - $sum % 10) % 10);
    }

    // credible spread: most owners have one or two places, a few groups, one or two chains
    private static function restaurantCounts(int $n): array
    {
        $counts = [];
        for ($i = 0; $i < $n; $i++) {
            $rng = new Rng("count:{$i}");
            $x = $rng->float();
            $counts[$i] = match (true) {
                $i === 0 => 28, $i === 1 => 21,
                $x < 0.72 => $rng->chance(0.7) ? 1 : 2,
                $x < 0.92 => $rng->int(3, 6),
                default => $rng->int(9, 12),
            };
        }

        return $counts;
    }

    // ---------------------------------------------------------------- places

    private static function branches(array $concept, int $count, array &$usedNames): array
    {
        $cities = Catalog::data('cities');
        $rng = new Rng("branches:{$concept['name']}");
        $weights = array_map(fn ($c) => $c[6], $cities['cities']);
        // a chain lives in a few home cities
        $home = [];
        while (count($home) < min(4, max(1, intdiv($count, 3) + 1))) {
            $home[$rng->weighted($weights)] = true;
        }
        $home = array_keys($home);

        $places = [];
        $taken = [];
        for ($b = 0; $b < $count; $b++) {
            $city = $count > 6 && $b >= 6 && $rng->chance(0.4) ? $rng->weighted($weights) : $home[$b % count($home)];
            $hoods = $cities['cities'][$city][5];
            $hood = $hoods[($b + $rng->int(0, 7)) % count($hoods)];
            foreach ([$hood, $city . ' ' . $hood, $city, $city . ' ' . ($b + 1)] as $suffix) {
                $name = $count === 1 && ! isset($usedNames[$concept['name']]) ? $concept['name'] : "{$concept['name']} {$suffix}";
                if (! isset($usedNames[$name]) && mb_strlen($name) <= 50 && ! isset($taken[$name])) {
                    break;
                }
            }
            $usedNames[$name] = $taken[$name] = true;
            $places[] = self::place($concept, $name, $city, $hood);
        }

        return $places;
    }

    private static function place(array $concept, string $name, string $city, string $hood): array
    {
        $data = Catalog::data('cities');
        [$canton, $postal, $prefix, $lat, $lng] = $data['cities'][$city];
        $rng = new Rng("place:{$name}");
        $segment = $concept['segment'];
        $tier = Catalog::tier($segment);
        $slug = Catalog::slug($name);
        $imageCategory = Catalog::data('types')[$concept['types'][0]] ?? 'restaurant';

        return [
            'name' => $name,
            'slug' => $slug,
            'concept' => $concept['name'],
            'cuisine' => $concept['cuisine'],
            'segment' => $segment,
            'description' => $concept['description'],
            'types' => $concept['types'],
            'price_range' => Catalog::PRICE_RANGE[$segment],
            'phone' => $prefix . ' ' . $rng->int(100, 999) . ' ' . $rng->int(10, 99) . ' ' . $rng->int(10, 99),
            'email' => str_replace('-', '', $slug) . '@example.com',
            'city' => $city,
            'neighbourhood' => $hood,
            'address' => $rng->pick($data['streets']) . ' ' . $rng->int(1, 180),
            'postal_code' => is_array($postal) ? $rng->pick($postal) : $postal,
            'province' => $canton,
            'latitude' => round($lat + $rng->between(-0.03, 0.03), 6),
            'longitude' => round($lng + $rng->between(-0.04, 0.04), 6),
            'delivery_fee' => match ($tier) { 'base' => $rng->pick([1.49, 1.99, 2.49, 2.99]), 'mid' => $rng->pick([1.99, 2.49, 2.99, 3.49]), 'high' => $rng->pick([0.0, 4.9, 6.9]) },
            'min_order' => match ($tier) { 'base' => $rng->pick([8, 10, 12]), 'mid' => $rng->pick([15, 18, 20]), 'high' => $rng->pick([40, 60, 80]) },
            'prep_minutes' => match ($tier) { 'base' => $rng->int(12, 25), 'mid' => $rng->int(25, 40), 'high' => $rng->int(45, 75) },
            'accepting_orders' => ! $rng->chance(0.05),
            'img' => $rng->chance(0.6)
                ? Catalog::image($imageCategory, $rng->int(0, 99), 'restaurant')
                : Catalog::image('restaurant', $rng->int(0, 99), 'restaurant'),
        ];
    }

    private static function legacyRestaurantNames(): array
    {
        preg_match_all('/"name" => "([^"]+)"/', file_get_contents(dirname(__DIR__) . '/RestaurantsTableSeeder.php'), $m);

        return $m[1];
    }
}
