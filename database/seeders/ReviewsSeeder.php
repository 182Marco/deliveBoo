<?php

namespace Database\Seeders;

use App\Order;
use App\Reviews\RestaurantRating;
use Database\Seeders\Support\ReviewWriter;
use Database\Seeders\Support\Rng;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Reviews of the delivered orders of registered customers (one per order at most), written a few
// hours after the delivery. Stars around the quality of the restaurant (its segment plus a noise of
// its own), an optional text composed by Support\ReviewWriter, sometimes the reply of the owner.
// About 2% of the texts are inappropriate on purpose (data/reviews_flagged.php), all visible: they
// demonstrate the moderation of the platform admin, the seeder never hides anything.
// The demo customer keeps his recent orders without a review (to try the "Review" button) except
// one, reviewed and answered (to try the edit). Deterministic (Rng keyed by email, restaurant and
// order time, never ids) and idempotent: orders that already have a review are skipped.
class ReviewsSeeder extends Seeder
{
    // latent quality of a restaurant by segment (the average its stars gather around)
    private const QUALITY = [
        'fast_food' => 3.6, 'budget' => 3.8, 'street_food' => 4.0, 'ethnic' => 4.0, 'trattoria' => 4.1,
        'traditional' => 4.1, 'premium' => 4.4, 'fine_dining' => 4.6, 'starred' => 4.75,
    ];
    private const DEFAULT_QUALITY = 3.9;

    private const REVIEW_CHANCE = 0.45;
    private const BAD_EVENING = 0.05;
    private const COMMENT_CHANCE = 0.4;
    // the restaurateur answers the unhappy customers more often
    private const REPLY_CHANCE = ['low' => 0.3, 'mid' => 0.3, 'high' => 0.1];
    // share of the comments that are inappropriate (at least one per category when possible)
    private const FLAGGED_SHARE = 0.02;
    // a text can appear at most this many times in the whole dataset, never twice in a restaurant
    private const MAX_SAME_TEXT = 3;
    private const RETRIES = 12;

    public function run(): void
    {
        $writer = ReviewWriter::fromFiles();
        $demo = (string) config('demo.accounts.customer.email');
        $owner = (string) config('demo.accounts.restaurateur.email');
        $orders = self::orders();
        if ($orders->isEmpty()) {
            return;
        }
        $plates = self::plates($orders->pluck('id')->all());
        $now = Carbon::now();
        $windowStart = $now->copy()->subDays((int) config('reviews.window_days'));

        // the demo customer: recent orders stay open for the "Review" button, except the second newest
        // (computed on all his recent orders, reviewed or not, so a second run changes nothing)
        $demoRecent = DB::table('orders as o')->join('users as u', 'u.id', '=', 'o.user_id')
            ->where('u.email', $demo)->where('o.status', Order::PAID)->where('o.fulfillment_status', Order::DELIVERED)
            ->where('o.delivered_at', '>=', $windowStart)
            ->orderByDesc('o.created_at')->pluck('o.id');
        $demoShowcase = $demoRecent->count() >= 2 ? $demoRecent[1] : null;
        $demoOpen = $demoRecent->reject(fn ($id) => $id === $demoShowcase)->flip();

        // texts already used: by restaurant (never twice) and overall (at most MAX_SAME_TEXT)
        $usedInRestaurant = DB::table('reviews')->whereNotNull('comment')->get(['restaurant_id', 'comment'])
            ->groupBy('restaurant_id')->map(fn ($rows) => $rows->pluck('comment')->flip()->all())->all();
        $usedOverall = DB::table('reviews')->whereNotNull('comment')->selectRaw('comment, COUNT(*) AS n')->groupBy('comment')->pluck('n', 'comment')->all();

        $rows = [];
        foreach ($orders as $o) {
            if (isset($demoOpen[$o->id])) {
                continue;
            }
            $key = "review:{$o->email}|{$o->restaurant}|{$o->created_at}";
            $rng = new Rng($key);
            $showcase = $o->id === $demoShowcase;
            if (! $showcase && ! $rng->chance(self::REVIEW_CHANCE)) {
                continue;
            }
            $rating = self::rating($o, $rng);
            $comment = null;
            if ($showcase || $rng->chance(self::COMMENT_CHANCE)) {
                $comment = self::uniqueComment($writer, $rng, $rating, $plates[$o->id] ?? [], $usedInRestaurant[$o->restaurant_id] ?? [], $usedOverall);
                $usedInRestaurant[$o->restaurant_id][$comment] = true;
                $usedOverall[$comment] = ($usedOverall[$comment] ?? 0) + 1;
            }
            $created = self::at(Carbon::parse($o->delivered_at)->addMinutes($showcase ? 120 : $rng->int(60, 72 * 60)), $now);
            $reply = null;
            $repliedAt = null;
            if ($comment !== null && ($showcase || $rng->chance(self::REPLY_CHANCE[ReviewWriter::band($rating)]))) {
                $reply = $writer->reply($rng, $rating, $o->name);
                $repliedAt = self::at($created->copy()->addMinutes($rng->int(90, 48 * 60)), $now);
            }
            $rows[] = [
                'key' => $key,
                'demo' => $o->email === $demo,
                'owner' => $o->owner === $owner,
                'row' => [
                    'restaurant_id' => $o->restaurant_id,
                    'user_id' => $o->user_id,
                    'order_id' => $o->id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'is_current' => true,
                    'reply' => $reply,
                    'replied_at' => $repliedAt,
                    'hidden_at' => null,
                    'created_at' => $created,
                    'updated_at' => $created,
                ],
            ];
        }

        $rows = $writer->flag($rows, self::FLAGGED_SHARE);

        DB::transaction(function () use ($rows) {
            foreach (array_chunk(array_column($rows, 'row'), 500) as $chunk) {
                DB::table('reviews')->insert($chunk);
            }
        });
        RestaurantRating::refreshAll();
    }

    // delivered orders of customer accounts without a review, oldest first (same order on every run)
    private static function orders()
    {
        return DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->join('restaurants as r', 'r.id', '=', 'o.restaurant_id')
            ->leftJoin('users as w', 'w.id', '=', 'r.user_id')
            ->where('u.role', 'customer')
            ->where('o.status', Order::PAID)->where('o.fulfillment_status', Order::DELIVERED)->whereNotNull('o.delivered_at')
            ->whereNotExists(fn ($q) => $q->from('reviews')->whereColumn('reviews.order_id', 'o.id'))
            // the history of the demo owner's group has its own reviews (DemoOwnerHistorySeeder)
            ->where(fn ($q) => $q->whereNull('o.transaction_id')->orWhere('o.transaction_id', 'not like', DemoOwnerHistorySeeder::TX_PREFIX . '%'))
            ->orderBy('o.created_at')->orderBy('u.email')->orderBy('r.name')
            ->get(['o.id', 'o.user_id', 'o.restaurant_id', 'o.created_at', 'o.delivered_at', 'u.email', 'u.name',
                'r.name as restaurant', 'r.segment', 'w.email as owner']);
    }

    // the dishes of every order (drinks excluded), by name: ":plate" picks one of them
    private static function plates(array $orderIds): array
    {
        $plates = [];
        foreach (array_chunk($orderIds, 900) as $ids) {
            DB::table('order_plate as op')->join('plates as p', 'p.id', '=', 'op.plate_id')
                ->whereIn('op.order_id', $ids)
                ->where(fn ($q) => $q->whereNull('p.course')->orWhereNotIn('p.course', OrderHistorySeeder::DRINK_COURSES))
                ->orderBy('p.name')
                ->get(['op.order_id', 'p.name'])
                ->each(function ($line) use (&$plates) {
                    $plates[$line->order_id][] = $line->name;
                });
        }

        return array_map(fn ($names) => array_values(array_unique($names)), $plates);
    }

    private static function rating(object $o, Rng $rng): int
    {
        $place = new Rng("quality:{$o->restaurant}");
        $quality = max(2.3, min(4.95, (self::QUALITY[$o->segment] ?? self::DEFAULT_QUALITY) + $place->between(-0.9, 0.8)));

        // now and then an evening goes wrong (late rider, wrong dish) whatever the place
        if ($rng->chance(self::BAD_EVENING)) {
            return $rng->int(1, 2);
        }

        return max(1, min(5, (int) round($quality + $rng->between(-1.2, 1.0))));
    }

    // a comment never used in this restaurant and not overused overall (the same Rng draws again)
    private static function uniqueComment(ReviewWriter $writer, Rng $rng, int $rating, array $plates, array $inRestaurant, array $overall): string
    {
        $text = $writer->comment($rng, $rating, $plates);
        for ($try = 0; $try < self::RETRIES && (isset($inRestaurant[$text]) || ($overall[$text] ?? 0) >= self::MAX_SAME_TEXT); $try++) {
            $text = $writer->comment($rng, $rating, $plates);
        }

        return $text;
    }

    // never in the future: the dataset ends today
    private static function at(Carbon $time, Carbon $now): Carbon
    {
        return $time->greaterThan($now) ? $now->copy()->subMinutes(5) : $time;
    }
}
