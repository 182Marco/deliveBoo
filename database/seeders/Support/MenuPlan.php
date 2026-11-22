<?php

namespace Database\Seeders\Support;

// The menu of a planned restaurant: dishes of its cuisine for the tier of its segment (with the
// lower tier as a fallback, at a markup), its signature dishes and drinks of its level and context.
final class MenuPlan
{
    // [min, max] number of items (drinks included) by segment
    private const SIZE = [
        'street_food' => [6, 14], 'fast_food' => [12, 30], 'budget' => [10, 28],
        'ethnic' => [18, 45], 'trattoria' => [30, 60], 'traditional' => [35, 75],
        'premium' => [50, 110], 'fine_dining' => [90, 160], 'starred' => [110, 220],
    ];

    // [min, max] number of drinks by tier
    private const DRINKS = ['base' => [3, 7], 'mid' => [8, 22], 'high' => [30, 70]];

    // cuisines whose menus are long whatever the segment: a sushi bar lists nigiri, sashimi,
    // maki, temaki, gunkan, fried dishes and starters
    private const CUISINE_SIZE = ['sushi' => [80, 150]];

    // fine dining and starred kitchens: few dishes (tasting menus and a short à la carte),
    // [food min, food max, drinks min, drinks max]: the wine list stays long
    private const SHORT_MENU = ['fine_dining' => [5, 10, 25, 45], 'starred' => [5, 10, 25, 45]];

    private const LOWER = ['high' => ['mid', 1.3], 'mid' => ['base', 1.15], 'base' => [null, 1.0]];

    /**
     * @return array<int, array{name: string, course: string, img: string, price: float, ingredients: string[], main: int,
     *               description: string, serves: int, from: ?int, to: ?int, calories: ?int, drink: bool}>
     */
    public static function build(array $restaurant): array
    {
        $profile = CatalogPlan::profile();
        $rng = new Rng("menu:{$restaurant['name']}");
        $cuisine = Catalog::cuisines()[$restaurant['cuisine']];
        $tier = Catalog::tier($restaurant['segment']);
        [$min, $max] = self::CUISINE_SIZE[$restaurant['cuisine']] ?? self::SIZE[$restaurant['segment']];
        $target = $rng->int($min, $max);
        $short = self::SHORT_MENU[$restaurant['segment']] ?? null;
        if ($profile['menu_max']) {
            $target = max($profile['menu_min'], min($target, $profile['menu_max']));
        }

        // drinks of the level and of the context of the cuisine
        $drinkPool = [];
        $tierIndex = ['base' => 0, 'mid' => 1, 'high' => 2][$tier];
        foreach (Catalog::data('drinks') as $d) {
            if ($d[4][$tierIndex] !== null && ($d[6] === 'all' || in_array($restaurant['cuisine'], $d[6], true))) {
                $drinkPool[] = $d;
            }
        }
        [$dMin, $dMax] = self::DRINKS[$tier];
        $drinkCount = $short
            ? min(count($drinkPool), $rng->int($short[2], $short[3]))
            : min(count($drinkPool), $rng->int($dMin, $dMax), max(2, intdiv($target, 2)));
        if ($profile['menu_max']) {
            $drinkCount = min($drinkCount, max(2, intdiv($target, 3)));
        }

        $items = [];
        // signature dishes of the concept first
        foreach (Catalog::concepts()[$restaurant['concept']]['signatures'] as $s) {
            $items[] = self::item($s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], [], $rng, 1.0);
        }

        // dishes: the classics at the top of the file are more likely, the rest adds variety
        $candidates = [];
        foreach ($cuisine['recipes'] as $pos => $recipe) {
            [$variants, $markup] = isset($recipe[3][$tier]) ? [$recipe[3][$tier], 1.0] : [null, 1.0];
            if ($variants === null && ($lower = self::LOWER[$tier][0]) && isset($recipe[3][$lower])) {
                [$variants, $markup] = [$recipe[3][$lower], self::LOWER[$tier][1]];
            }
            if ($variants !== null) {
                $candidates[] = [$recipe, is_array($variants[0]) ? $variants : [$variants], $markup, $markup > 1 ? 0.5 : 1.0 / (1 + $pos / 12)];
            }
        }
        $foodTarget = $short
            ? max(0, min($rng->int($short[0], $short[1]), $profile['menu_max'] ?? PHP_INT_MAX) - count($items))
            : max(1, $target - $drinkCount - count($items));
        $weights = array_map(fn ($c) => $c[3], $candidates);
        while (count($items) < $foodTarget + count(Catalog::concepts()[$restaurant['concept']]['signatures']) && $weights) {
            $k = $rng->weighted($weights);
            unset($weights[$k]);
            [$recipe, $variants, $markup] = $candidates[$k];
            $v = $rng->pick($variants);
            $items[] = self::item($recipe[0], $recipe[1], $recipe[2], $v[0], $v[1], $v[2], $v[3], $recipe[4] ?? [], $rng, $markup);
        }

        // water first, then a sample of the other drinks
        $water = array_values(array_filter($drinkPool, fn ($d) => $d[1] === 'water'));
        $others = $rng->shuffle(array_values(array_filter($drinkPool, fn ($d) => $d[1] !== 'water')));
        foreach (array_slice([...array_slice($water, 0, 2), ...$others], 0, $drinkCount) as $d) {
            $items[] = [...self::item($d[0], $d[1], $d[2], $d[4][$tierIndex], $d[3], 1, $d[5], [], $rng, 1.0, 0.03), 'drink' => true];
        }

        // one name per menu
        $unique = [];
        foreach ($items as $item) {
            $unique[$item['name']] ??= $item;
        }

        return array_values($unique);
    }

    private static function item(string $name, string $course, string $img, float $anchor, string $ingredients, int $main,
        string $description, array $opts, Rng $rng, float $markup, float $jitter = 0.08): array
    {
        return [
            'name' => $name,
            'course' => $course,
            'img' => $img,
            'price' => self::price($anchor * $markup * (1 + $rng->between(-$jitter, $jitter))),
            'ingredients' => Catalog::ingredientNames($ingredients),
            'main' => $main,
            'description' => $description,
            'serves' => $opts['serves'] ?? 1,
            'from' => $opts['season'][0] ?? null,
            'to' => $opts['season'][1] ?? null,
            'calories' => $opts['cal'] ?? null,
            'drink' => false,
        ];
    }

    // menu prices: steps of 0.50 (whole euros above 30)
    public static function price(float $value): float
    {
        return $value >= 30 ? (float) round($value) : max(0.5, round($value * 2) / 2);
    }
}
