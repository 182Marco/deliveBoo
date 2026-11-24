<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Restaurant;
use App\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Partner hub: the reviews of one restaurant of the logged-in restaurateur (RestaurantPolicy@manage)
// and his public reply. Replies do not change the rating, so they need no RestaurantRating::write.
// Reviews hidden by the platform moderation are listed with a badge and cannot be answered.
class ReviewController extends Controller
{
    public const PER_PAGE = 5;

    public function index(Request $request, string $slug): View
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();
        if ($request->user()->cannot('manage', $restaurant)) {
            return view('admin.notYours.restaurant');
        }

        $stars = (int) $request->query('stars');
        $stars = $stars >= 1 && $stars <= 5 ? $stars : null;
        $unanswered = $request->boolean('unanswered');

        $reviews = $restaurant->reviews()
            ->with('user:id,name,last_name', 'order:id,created_at')
            ->when($stars, fn ($q, $n) => $q->where('rating', $n))
            ->when($unanswered, fn ($q) => $q->whereNull('reply')->whereNotNull('comment')->whereNull('hidden_at'))
            ->latest()->latest('id')
            ->paginate(self::PER_PAGE)->withQueryString();

        $distribution = Review::counted()->where('restaurant_id', $restaurant->id)
            ->selectRaw('rating, COUNT(*) AS n')->groupBy('rating')->pluck('n', 'rating');

        return view('admin.reviews.index', [
            'restaurant' => $restaurant,
            'reviews' => $reviews,
            'distribution' => collect(range(5, 1))->mapWithKeys(fn ($n) => [$n => (int) ($distribution[$n] ?? 0)])->all(),
            'unansweredCount' => $restaurant->reviews()->visible()->whereNotNull('comment')->whereNull('reply')->count(),
            'stars' => $stars,
            'unanswered' => $unanswered,
        ]);
    }

    public function reply(Request $request, int $id): RedirectResponse|View
    {
        $review = Review::with('restaurant')->findOrFail($id);
        if ($review->restaurant === null || $request->user()->cannot('manage', $review->restaurant)) {
            return view('admin.notYours.restaurant');
        }
        abort_if($review->isHidden(), 403);

        $data = $request->validate([
            'reply' => ['required', 'string', 'max:'.config('reviews.comment_max')],
        ], [], ['reply' => __('partner.reviews.replyLabel')]);

        $review->reply = trim($data['reply']);
        $review->replied_at = now();
        $review->save();

        return redirect()->to(url()->previous(route('admin.reviews.index', $review->restaurant->slug)).'#review-'.$review->id)
            ->with('reviewReplied', true);
    }

    public function deleteReply(Request $request, int $id): RedirectResponse|View
    {
        $review = Review::with('restaurant')->findOrFail($id);
        if ($review->restaurant === null || $request->user()->cannot('manage', $review->restaurant)) {
            return view('admin.notYours.restaurant');
        }

        $review->reply = null;
        $review->replied_at = null;
        $review->save();

        return redirect()->to(url()->previous(route('admin.reviews.index', $review->restaurant->slug)).'#review-'.$review->id)
            ->with('reviewReplyDeleted', true);
    }
}
