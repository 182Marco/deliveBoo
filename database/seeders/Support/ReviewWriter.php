<?php

namespace Database\Seeders\Support;

// Composes the texts of the generated reviews from data/reviews.php (normal, curated sentences) and
// data/reviews_flagged.php (inappropriate texts for the moderation demo). Pure and deterministic:
// every choice comes from the Rng it receives, never from the database or the clock.
final class ReviewWriter
{
    public const BANDS = ['low', 'mid', 'high'];
    public const FLAG_CATEGORIES = ['vulgar', 'off_topic', 'nonsense', 'flirting'];

    public function __construct(private readonly array $texts, private readonly array $flagged)
    {
    }

    public static function fromFiles(): self
    {
        return new self(
            require database_path('seeders/data/reviews.php'),
            require database_path('seeders/data/reviews_flagged.php'),
        );
    }

    public function texts(): array
    {
        return $this->texts;
    }

    public function flaggedTexts(): array
    {
        return $this->flagged;
    }

    /** low = 1-2 stars, mid = 3, high = 4-5 */
    public static function band(int $rating): string
    {
        return $rating <= 2 ? 'low' : ($rating === 3 ? 'mid' : 'high');
    }

    /**
     * One to two sentences on different topics of the band of the rating, sometimes with an opening
     * and a closing sentence. ":plate" sentences only when the order has dishes, filled with one of them.
     * $prefer: topics of which one is always used (none given = the same draws as before).
     */
    public function comment(Rng $rng, int $rating, array $plates, array $prefer = []): string
    {
        $band = self::band($rating);
        $topics = $this->texts['topics'][$band];
        if (! $plates) {
            unset($topics['plate']);
        }
        $chosen = array_slice($rng->shuffle(array_keys($topics)), 0, $rng->chance(0.55) ? 2 : 1);
        // the cause of the rating, when the caller knows it (late delivery, a weak dish...), comes first
        $prefer = array_values(array_intersect($prefer, array_keys($topics)));
        if ($prefer) {
            $first = $rng->pick($prefer);
            $chosen = array_slice(array_values(array_unique([$first, ...$chosen])), 0, count($chosen));
        }

        $parts = [];
        if ($rng->chance(0.4)) {
            $parts[] = $rng->pick($this->texts['openers'][$band]);
        }
        foreach ($chosen as $topic) {
            $parts[] = $rng->pick($topics[$topic]);
        }
        if ($rng->chance(0.35)) {
            $parts[] = $rng->pick($this->texts['closers'][$band]);
        }
        $text = implode(' ', $parts);

        return $plates ? str_replace(':plate', $rng->pick($plates), $text) : $text;
    }

    /** public reply of the restaurateur, addressed to the customer's first name */
    public function reply(Rng $rng, int $rating, string $name): string
    {
        return str_replace(':name', $name, $rng->pick($this->texts['replies'][self::band($rating)]));
    }

    /**
     * Replaces the text of some commented reviews with an inappropriate one: about $share of the
     * comments (at least one per category, never more than the distinct texts available), at most
     * one per restaurant, categories in rotation and star bands alternated. Never the demo customer;
     * the demo owner gets the first one, so the partner hub shows it too. No reply. Choice by a hash of the natural key: same result every run.
     * Rows: ['key' => natural key, 'demo' => bool, 'owner' => bool, 'row' => [restaurant_id, rating, comment, reply, replied_at...]].
     */
    public function flag(array $rows, float $share): array
    {
        $candidates = array_filter($rows, fn ($r) => $r['row']['comment'] !== null && ! $r['demo']);
        if (! $candidates) {
            return $rows;
        }
        $available = array_sum(array_map('count', $this->flagged));
        $wanted = min($available, max(count(self::FLAG_CATEGORIES), (int) round(count($candidates) * $share)));
        uasort($candidates, fn ($a, $b) => hash('sha1', 'flag:'.$a['key']) <=> hash('sha1', 'flag:'.$b['key']));
        // only the first slot goes to the demo owner (when he has a candidate)
        $first = array_key_first(array_filter($candidates, fn ($c) => $c['owner']));
        if ($first !== null) {
            $candidates = [$first => $candidates[$first]] + $candidates;
        }

        $usedRestaurants = [];
        $bands = ['low', 'high', 'mid'];
        // every category in its own fixed order: different texts until a category runs out
        $order = [];
        foreach (self::FLAG_CATEGORIES as $category) {
            $order[$category] = (new Rng("flag-order:{$category}"))->shuffle(array_keys($this->flagged[$category]));
        }
        for ($slot = 0; $slot < $wanted; $slot++) {
            $pick = null;
            foreach ([true, false] as $sameBand) {
                foreach ($candidates as $i => $c) {
                    if (isset($usedRestaurants[$c['row']['restaurant_id']])) {
                        continue;
                    }
                    if ($sameBand && self::band($c['row']['rating']) !== $bands[$slot % 3]) {
                        continue;
                    }
                    $pick = $i;
                    break 2;
                }
            }
            if ($pick === null) {
                break;
            }
            $categories = count(self::FLAG_CATEGORIES);
            $category = self::FLAG_CATEGORIES[$slot % $categories];
            $texts = $order[$category];
            $index = intdiv($slot, $categories);
            if ($index >= count($texts)) {
                continue;
            }
            $rows[$pick]['row']['comment'] = $this->flagged[$category][$texts[$index]];
            $rows[$pick]['row']['reply'] = null;
            $rows[$pick]['row']['replied_at'] = null;
            $usedRestaurants[$rows[$pick]['row']['restaurant_id']] = true;
            unset($candidates[$pick]);
        }

        return $rows;
    }

    /** placeholders left in a text (":plate", ":name"...): a finished text has none */
    public static function placeholders(string $text): array
    {
        preg_match_all('/:[a-z_]+/', $text, $m);

        return $m[0];
    }
}
