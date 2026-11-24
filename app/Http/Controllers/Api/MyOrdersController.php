<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Order;
use App\Plate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// "My orders" of the Vue site (dev/PIANO_AUTH_CLIENTI.md, step 7.2): only the orders of the logged-in
// account, found through orders.user_id (never by email), newest first, paginated.
// Guests get 401 (auth:sanctum), banned accounts are logged out (active).
class MyOrdersController extends Controller
{
    public const PER_PAGE = 5;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = $user->orders()
            ->with(['restaurant:id,name,slug,img', 'plates:id,name', 'review'])
            ->latest()
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->through(fn (Order $order) => [
                ...self::payload($order),
                // the customer's review and whether the "Review" button writes or edits it (OrderPolicy)
                'review' => $order->review?->forAuthor(),
                'can_review' => $user->can('review', $order),
            ]);

        return response()->json($orders);
    }

    /** what the page shows: no customer data (it is the account's own), no payment errors or driver */
    public static function payload(Order $order): array
    {
        return [
            'id' => $order->id,
            'placed_at' => $order->created_at?->toIso8601String(),
            'delivered_at' => $order->delivered_at?->toIso8601String(),
            'price' => $order->price,
            'status' => $order->status,
            'fulfillment_status' => $order->fulfillment_status,
            'address' => $order->customer_address,
            // null when the restaurant was deleted: the order stays in the history
            'restaurant' => $order->restaurant?->only(['name', 'slug', 'img']),
            'lines' => $order->plates->map(fn (Plate $plate) => [
                'name' => $plate->name,
                'quantity' => (int) $plate->pivot->quantity,
                'unit_price' => (float) $plate->pivot->unit_price,
            ])->values()->all(),
        ];
    }
}
