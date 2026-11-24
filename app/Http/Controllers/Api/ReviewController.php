<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveReviewRequest;
use App\Order;
use App\Review;
use App\Reviews\RestaurantRating;
use Illuminate\Http\JsonResponse;

// "Review" button of "My orders": writes or edits the review of one delivered order of the account.
// Who may write it is OrderPolicy@review (owner, customer, delivered, within the window, not hidden).
// Guests get 401 (auth:sanctum), banned accounts are logged out (active).
class ReviewController extends Controller
{
    public function upsert(SaveReviewRequest $request, int $order): JsonResponse
    {
        $model = Order::with('review')->findOrFail($order);
        $this->authorize('review', $model);

        $review = RestaurantRating::write($model->restaurant_id, function () use ($request, $model) {
            // read again under the restaurant lock: an admin may have hidden it meanwhile,
            // and a double submit finds the row of the first one (order_id is unique)
            $review = Review::where('order_id', $model->id)->first() ?? new Review();
            abort_if($review->isHidden(), 403);
            $review->fill($request->validated());
            $review->order_id = $model->id;
            $review->user_id = $model->user_id;
            $review->restaurant_id = $model->restaurant_id;
            $review->save();

            return $review;
        });

        return response()->json(['review' => $review->forAuthor(), 'can_review' => true]);
    }
}
