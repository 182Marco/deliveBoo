<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Restaurant;
use App\Review;
use Illuminate\Http\JsonResponse;

// Reviews of the restaurant page: the summary of the counted reviews (stored average and count, how
// many reviews per star) and the visible reviews with a text, newest first. The author is shown by
// first name and initial of the last name with the avatar: never email or other account data.
class RestaurantReviewController extends Controller
{
    public const PER_PAGE = 4;

    public function index(string $slug): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        $stars = Review::counted()->where('restaurant_id', $restaurant->id)
            ->selectRaw('rating, COUNT(*) AS n')->groupBy('rating')->pluck('n', 'rating');

        $reviews = Review::visible()->where('restaurant_id', $restaurant->id)->whereNotNull('comment')
            ->with('user:id,name,last_name,avatar')
            ->latest()->latest('id')
            ->paginate(self::PER_PAGE)
            ->through(fn (Review $review) => self::payload($review));

        return response()->json([
            'summary' => [
                'average' => $restaurant->rating_avg,
                'count' => $restaurant->rating_count,
                'distribution' => collect(range(5, 1))->mapWithKeys(fn ($n) => [$n => (int) ($stars[$n] ?? 0)]),
            ],
            'reviews' => $reviews,
        ]);
    }

    public static function payload(Review $review): array
    {
        $user = $review->user;

        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
            'created_at' => $review->created_at?->toIso8601String(),
            // null for an anonymous review (account deleted, legacy votes)
            'author' => $user ? [
                'name' => trim($user->name.' '.mb_substr((string) $user->last_name, 0, 1).($user->last_name ? '.' : '')),
                'avatar_seed' => $user->avatarSeed(),
                'avatar_overrides' => (object) $user->avatarOverrides(),
            ] : null,
            'reply' => $review->reply,
            'replied_at' => $review->replied_at?->toIso8601String(),
        ];
    }
}
