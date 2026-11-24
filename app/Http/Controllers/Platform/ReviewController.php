<?php

namespace App\Http\Controllers\Platform;

use App\AuditLog;
use App\Http\Controllers\Controller;
use App\Review;
use App\Reviews\RestaurantRating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Moderation of the reviews by the platform admin (Tables > Reviews). Hiding removes a review from
// the restaurant page and from its average; the customer can no longer edit it (OrderPolicy@review).
// Every action leaves a row in the audit log. Only the admin moderates: seeders never hide anything.
class ReviewController extends Controller
{
    public function hide(Request $request, int $review): RedirectResponse
    {
        return $this->moderate($request, $review, true);
    }

    public function unhide(Request $request, int $review): RedirectResponse
    {
        return $this->moderate($request, $review, false);
    }

    private function moderate(Request $request, int $id, bool $hide): RedirectResponse
    {
        $review = Review::with('restaurant:id,name')->findOrFail($id);

        $changed = RestaurantRating::write((int) $review->restaurant_id, function () use ($review, $hide, $request) {
            $review->refresh();
            if ($review->isHidden() === $hide) {
                return false;
            }
            $review->hidden_at = $hide ? now() : null;
            $review->hidden_by = $hide ? $request->user()->id : null;
            $review->save();

            return true;
        });

        if ($changed) {
            AuditLog::record($hide ? AuditLog::REVIEW_HIDDEN : AuditLog::REVIEW_UNHIDDEN, $review, [
                'restaurant' => $review->restaurant?->name,
                'rating' => $review->rating,
                'comment' => Str::limit((string) $review->comment, 120),
            ]);
        }

        return redirect()->to(url()->previous(route('platform.tables', ['section' => 'reviews'])))
            ->with('status', __($hide ? 'platform.reviews.flash.hidden' : 'platform.reviews.flash.unhidden'));
    }
}
