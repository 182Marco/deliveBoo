<?php

namespace App\Reviews;

use App\Restaurant;
use Illuminate\Support\Facades\DB;

/**
 * The only writer of the rating columns of a restaurant (rating_avg, rating_count, rating_score)
 * and of reviews.is_current. Everything is recomputed from the review rows, never incremented,
 * so the stored values always match the rows whatever happened before.
 *
 * - rating_avg    arithmetic mean of the counted reviews (the number shown everywhere)
 * - rating_count  how many reviews are counted
 * - rating_score  Bayesian mean (config/reviews.php), used ONLY to rank "Guest favourites"
 *
 * Counted = not hidden by the moderation and the latest visible review of its author for that
 * restaurant (the order placed last wins), so a customer who orders every week weighs one vote.
 * Anonymous legacy rows (no author) always count.
 */
final class RestaurantRating
{
    /**
     * Runs a change of the reviews of a restaurant and refreshes its rating, atomically: the
     * restaurant row is locked first, so two writes on the same restaurant run one after the other
     * and the last one computes the aggregates from all the rows.
     */
    public static function write(int $restaurantId, callable $change): mixed
    {
        return DB::transaction(function () use ($restaurantId, $change) {
            Restaurant::whereKey($restaurantId)->lockForUpdate()->value('id');
            $result = $change();
            self::refresh($restaurantId);

            return $result;
        });
    }

    /** recompute is_current and the aggregates of one restaurant from its review rows */
    public static function refresh(int $restaurantId): void
    {
        self::markCurrent($restaurantId);

        $row = DB::table('reviews')->where('restaurant_id', $restaurantId)
            ->whereNull('hidden_at')->where('is_current', true)
            ->selectRaw('COUNT(*) AS n, COALESCE(SUM(rating), 0) AS total')->first();

        DB::table('restaurants')->where('id', $restaurantId)->update(self::columns((int) $row->n, (float) $row->total));
    }

    /** every restaurant (migration, seeders, php artisan foodsy:refresh-ratings) */
    public static function refreshAll(): void
    {
        DB::table('restaurants')->orderBy('id')->pluck('id')
            ->each(fn ($id) => DB::transaction(fn () => self::refresh((int) $id)));
    }

    /** the stored aggregates for n counted reviews whose stars add up to $total */
    public static function columns(int $count, float $total): array
    {
        $weight = (float) config('reviews.prior_weight');
        $mean = (float) config('reviews.prior_mean');

        return [
            'rating_count' => $count,
            'rating_avg' => $count > 0 ? round($total / $count, 2) : null,
            'rating_score' => $count > 0 ? round(($weight * $mean + $total) / ($weight + $count), 3) : null,
        ];
    }

    // for every author: only the review of the most recent order (among the visible ones) is current
    private static function markCurrent(int $restaurantId): void
    {
        $rows = DB::table('reviews as v')
            ->leftJoin('orders as o', 'o.id', '=', 'v.order_id')
            ->where('v.restaurant_id', $restaurantId)->whereNotNull('v.user_id')
            ->orderBy('v.user_id')
            ->orderByRaw('CASE WHEN v.hidden_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc(DB::raw('COALESCE(o.created_at, v.created_at)'))
            ->orderByDesc('v.id')
            ->get(['v.id', 'v.user_id', 'v.is_current']);

        $current = [];
        foreach ($rows as $row) {
            $current[$row->user_id] ??= (int) $row->id;
        }
        $wanted = array_flip($current);
        $off = $rows->filter(fn ($r) => (bool) $r->is_current && ! isset($wanted[$r->id]))->pluck('id')->all();
        $on = $rows->filter(fn ($r) => ! $r->is_current && isset($wanted[$r->id]))->pluck('id')->all();

        foreach (array_chunk($off, 500) as $ids) {
            DB::table('reviews')->whereIn('id', $ids)->update(['is_current' => false]);
        }
        foreach (array_chunk($on, 500) as $ids) {
            DB::table('reviews')->whereIn('id', $ids)->update(['is_current' => true]);
        }
    }
}
