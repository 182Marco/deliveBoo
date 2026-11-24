<?php

namespace Tests\Feature\Seeders;

use Database\Seeders\Support\ReviewWriter;
use Database\Seeders\Support\Rng;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Quality of the seeded review texts (data/reviews.php, data/reviews_flagged.php, Support\ReviewWriter):
 * the normal texts are clean, on topic, complete and varied; the inappropriate ones exist only for the
 * moderation demo, in the four categories, spread out and always visible until an admin hides them.
 */
class ReviewTextsTest extends TestCase
{
    use RefreshDatabase;

    // words that must never appear in the normal texts (whole words, case-insensitive)
    private const BLOCKLIST = [
        'crap', 'shit', 'bullshit', 'damn', 'hell', 'idiot', 'idiots', 'moron', 'morons', 'bastard', 'bastards', 'jerk',
        'stupid', 'dumb', 'screw', 'fuck', 'fucking', 'ass', 'garbage', 'clown', 'clowns', 'crook', 'pathetic', 'sucks',
        'cute', 'gorgeous', 'handsome', 'flirt', 'crypto', 'referee', 'dentist', 'landlord', 'banana',
    ];

    // at least one of these in every composed comment: it is about food delivery
    private const DOMAIN = [
        'food', 'dish', 'meal', 'order', 'deliver', 'rider', 'courier', 'portion', 'pack', 'container', 'lid', 'bag',
        'sauce', 'menu', 'kitchen', 'restaurant', 'tast', 'flavour', 'season', 'salt', 'hot', 'warm', 'cold', 'fresh',
        'price', 'value', 'expensive', 'pricey', 'cheap', 'fee', 'cutlery', 'drink', 'rice', 'side', 'box', 'ingredient',
        'cook', 'crisp', 'steaming', 'temperature', 'reheat', 'spill', 'leak', 'plastic', 'dressing', 'napkin', 'lunch', 'dinner',
    ];

    private function writer(): ReviewWriter
    {
        return ReviewWriter::fromFiles();
    }

    /** every sentence of the normal pool, with its place */
    private function normalSentences(): array
    {
        $texts = $this->writer()->texts();
        $out = [];
        foreach ($texts['topics'] as $band => $topics) {
            foreach ($topics as $topic => $list) {
                foreach ($list as $s) {
                    $out["topics.{$band}.{$topic}"][] = $s;
                }
            }
        }
        foreach (['openers', 'closers', 'replies'] as $group) {
            foreach ($texts[$group] as $band => $list) {
                $out["{$group}.{$band}"] = $list;
            }
        }

        return $out;
    }

    private function blocked(string $text): array
    {
        preg_match_all('/[a-z\']+/', mb_strtolower($text), $m);

        return array_values(array_intersect($m[0], self::BLOCKLIST));
    }

    // a domain word, or the name of a dish of the order (":plate" filled)
    private function onTopic(string $text, array $plates = []): bool
    {
        $lower = mb_strtolower($text);
        foreach ([...self::DOMAIN, ...array_map('mb_strtolower', $plates)] as $term) {
            if (str_contains($lower, $term)) {
                return true;
            }
        }

        return false;
    }

    public function testTheNormalPoolIsCleanCompleteAndWithoutDuplicates()
    {
        $texts = $this->writer()->texts();
        foreach (ReviewWriter::BANDS as $band) {
            foreach (['food', 'plate', 'portion', 'temperature', 'delivery', 'packaging', 'value', 'service'] as $topic) {
                $this->assertGreaterThanOrEqual(5, count($texts['topics'][$band][$topic] ?? []), "{$band}.{$topic}");
            }
            $this->assertNotEmpty($texts['openers'][$band]);
            $this->assertNotEmpty($texts['closers'][$band]);
            $this->assertNotEmpty($texts['replies'][$band]);
        }

        $all = [];
        foreach ($this->normalSentences() as $where => $list) {
            foreach ($list as $sentence) {
                $this->assertIsString($sentence, $where);
                $this->assertSame(trim($sentence), $sentence, $where);
                $this->assertGreaterThanOrEqual(10, mb_strlen($sentence), "{$where}: too short");
                $this->assertSame([], $this->blocked($sentence), "{$where}: {$sentence}");
                $this->assertMatchesRegularExpression('/[.!?]$/', $sentence, "{$where}: {$sentence}");
                $all[] = $sentence;
            }
        }
        $this->assertSame(count($all), count(array_unique($all)), 'a sentence of the normal pool is repeated');
    }

    public function testPlaceholdersAreOnlyWhereTheyCanBeFilled()
    {
        foreach ($this->normalSentences() as $where => $list) {
            foreach ($list as $sentence) {
                $placeholders = ReviewWriter::placeholders($sentence);
                $allowed = match (true) {
                    str_ends_with($where, '.plate') => [':plate'],
                    str_starts_with($where, 'replies.') => [':name'],
                    default => [],
                };
                $this->assertSame([], array_diff($placeholders, $allowed), "{$where}: {$sentence}");
                if ($allowed) {
                    $this->assertNotEmpty($placeholders, "{$where} must use {$allowed[0]}: {$sentence}");
                }
            }
        }
        // topic sentences talk about food delivery by themselves
        foreach ($this->writer()->texts()['topics'] as $band => $topics) {
            foreach ($topics as $topic => $list) {
                foreach ($list as $sentence) {
                    $this->assertTrue($this->onTopic(str_replace(':plate', 'dish', $sentence)), "{$band}.{$topic} off topic: {$sentence}");
                }
            }
        }
    }

    public function testComposedCommentsAndRepliesAreFinishedOnTopicAndVaried()
    {
        $writer = $this->writer();
        $plates = ['Margherita', 'Salmon nigiri', 'Pad thai', 'Falafel wrap'];
        $comments = [];
        for ($i = 0; $i < 3000; $i++) {
            $rng = new Rng("texts-test:{$i}");
            $rating = $rng->int(1, 5);
            $ordered = $i % 5 === 0 ? [] : [$rng->pick($plates)];
            $comment = $writer->comment($rng, $rating, $ordered);
            $reply = $writer->reply($rng, $rating, 'Anna');

            foreach ([$comment, $reply] as $text) {
                $this->assertSame([], ReviewWriter::placeholders($text), $text);
                $this->assertGreaterThanOrEqual(15, mb_strlen($text), $text);
                $this->assertSame([], $this->blocked($text), $text);
            }
            $this->assertTrue($this->onTopic($comment, $ordered), $comment);
            $this->assertStringContainsString('Anna', $reply);
            $comments[] = $comment;
        }
        // no text dominates: the most frequent one is at most 1% of the comments
        $this->assertLessThanOrEqual(30, max(array_count_values($comments)));
        $this->assertGreaterThan(1500, count(array_unique($comments)));
        // the same Rng key always writes the same comment
        $this->assertSame($writer->comment(new Rng('same'), 4, ['Pad thai']), $writer->comment(new Rng('same'), 4, ['Pad thai']));
    }

    public function testTheFlaggedPoolHasTheFourCategories()
    {
        $flagged = $this->writer()->flaggedTexts();
        $this->assertSame(ReviewWriter::FLAG_CATEGORIES, array_keys($flagged));
        $all = [];
        foreach ($flagged as $category => $list) {
            $this->assertGreaterThanOrEqual(6, count(array_unique($list)), $category);
            foreach ($list as $text) {
                $this->assertGreaterThanOrEqual(15, mb_strlen(trim($text)), $category);
                $this->assertSame([], ReviewWriter::placeholders($text), $text);
                $all[] = $text;
            }
        }
        $this->assertSame(count($all), count(array_unique($all)));
        // they never leak into the normal pool
        $normal = array_merge(...array_values($this->normalSentences()));
        foreach ($all as $text) {
            $this->assertNotContains($text, $normal);
        }
    }

    public function testFlagSelectionOnALargeDatasetIsSpreadOut()
    {
        // synthetic reviews: 60 restaurants, 1500 comments, a few by the demo customer and the demo owner
        $rows = [];
        for ($i = 0; $i < 1500; $i++) {
            $rng = new Rng("flag-test:{$i}");
            $rows[] = [
                'key' => "flag-test:{$i}",
                'demo' => $i % 50 === 0,
                'owner' => $i % 60 === 7,
                'row' => ['restaurant_id' => $i % 60, 'rating' => $rng->int(1, 5), 'comment' => "Normal text {$i} about the food.", 'reply' => 'A reply.', 'replied_at' => '2026-01-01'],
            ];
        }
        $writer = $this->writer();
        $category = [];
        foreach ($writer->flaggedTexts() as $name => $list) {
            foreach ($list as $text) {
                $category[$text] = $name;
            }
        }

        $result = $writer->flag($rows, 0.02);
        $flagged = array_values(array_filter($result, fn ($r) => isset($category[$r['row']['comment']])));

        $this->assertSame($result, $writer->flag($rows, 0.02), 'not deterministic');
        $this->assertGreaterThanOrEqual(25, count($flagged));
        $this->assertLessThanOrEqual(0.03 * 1500, count($flagged));
        $this->assertEqualsCanonicalizing(ReviewWriter::FLAG_CATEGORIES, array_values(array_unique(array_map(fn ($r) => $category[$r['row']['comment']], $flagged))));
        $restaurants = array_column(array_column($flagged, 'row'), 'restaurant_id');
        $this->assertSame(count($restaurants), count(array_unique($restaurants)), 'two flagged reviews in one restaurant');
        $this->assertGreaterThanOrEqual(3, count(array_unique(array_map(fn ($r) => ReviewWriter::band($r['row']['rating']), $flagged))));
        $texts = array_column(array_column($flagged, 'row'), 'comment');
        $this->assertSame(count($texts), count(array_unique($texts)), 'a flagged text is repeated');
        foreach ($flagged as $r) {
            $this->assertFalse($r['demo'], 'the demo customer never writes a flagged review');
            $this->assertNull($r['row']['reply']);
            $this->assertNull($r['row']['replied_at']);
        }
        $this->assertNotEmpty(array_filter($flagged, fn ($r) => $r['owner']), 'the demo owner gets one, for the partner hub');
        // everything else is untouched
        $this->assertSame(1500 - count($flagged), count(array_filter($result, fn ($r) => str_starts_with($r['row']['comment'], 'Normal text'))));
    }

    public function testTheSeededReviewsFollowTheRules()
    {
        $writer = $this->writer();
        $flaggedTexts = array_merge(...array_values($writer->flaggedTexts()));
        $reviews = DB::table('reviews')->get();
        $this->assertNotEmpty($reviews);

        // comment and reply: null or a real text, never an empty string
        $this->assertSame(0, $reviews->filter(fn ($r) => $r->comment !== null && mb_strlen(trim($r->comment)) < 15)->count());
        $this->assertSame(0, $reviews->filter(fn ($r) => $r->reply !== null && mb_strlen(trim($r->reply)) < 15)->count());

        $comments = $reviews->whereNotNull('comment');
        $normal = $comments->reject(fn ($r) => in_array($r->comment, $flaggedTexts, true));
        $plates = DB::table('order_plate as op')->join('plates as p', 'p.id', '=', 'op.plate_id')
            ->whereIn('op.order_id', $normal->pluck('order_id'))->get(['op.order_id', 'p.name'])->groupBy('order_id');
        foreach ($normal as $r) {
            $this->assertSame([], ReviewWriter::placeholders($r->comment), $r->comment);
            $this->assertSame([], $this->blocked($r->comment), $r->comment);
            $this->assertTrue($this->onTopic($r->comment, $plates->get($r->order_id, collect())->pluck('name')->all()), $r->comment);
        }
        foreach ($reviews->whereNotNull('reply') as $r) {
            $this->assertSame([], ReviewWriter::placeholders($r->reply), $r->reply);
        }
        // never the same text twice in a restaurant, never more than 3 times overall
        foreach ($comments->groupBy('restaurant_id') as $restaurant => $rows) {
            $this->assertSame($rows->count(), $rows->pluck('comment')->unique()->count(), "repeated text in restaurant {$restaurant}");
        }
        $this->assertLessThanOrEqual(3, $comments->countBy('comment')->max());

        // inappropriate reviews: visible, without reply, at most one per restaurant, never by the demo customer
        $flagged = $comments->filter(fn ($r) => in_array($r->comment, $flaggedTexts, true));
        $demo = DB::table('users')->where('email', config('demo.accounts.customer.email'))->value('id');
        // the small test profile may have no comment of another customer; the full dataset has 28 (dev/PIANO_DATASET.md)
        if ($comments->where('user_id', '!=', $demo)->isNotEmpty()) {
            $this->assertNotEmpty($flagged);
        }
        $this->assertLessThan(max(1, $normal->count()), $flagged->count());
        $this->assertSame(0, $flagged->whereNotNull('hidden_at')->count());
        $this->assertSame(0, $flagged->whereNotNull('reply')->count());
        $this->assertSame($flagged->count(), $flagged->pluck('restaurant_id')->unique()->count());
        $this->assertSame(0, $flagged->where('user_id', $demo)->count());
    }
}
