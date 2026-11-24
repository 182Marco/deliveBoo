<?php

namespace App\Policies;

use App\Order;
use App\User;

// reviews: only the customer who placed a delivered order, within config('reviews.window_days')
// from the delivery. A review hidden by the moderation can never be edited again (otherwise
// rewriting it would bring it back).
class OrderPolicy
{
    public function review(User $user, Order $order): bool
    {
        return self::reviewable($user, $order) && ! ($order->review?->isHidden() ?? false);
    }

    // everything except the moderation: the order itself allows a review
    private static function reviewable(User $user, Order $order): bool
    {
        return $user->hasRole(User::CUSTOMER)
            && ! $user->isBanned()
            && $order->user_id !== null && (int) $order->user_id === (int) $user->id
            && $order->restaurant_id !== null
            && $order->status === Order::PAID
            && $order->fulfillment_status === Order::DELIVERED
            && $order->delivered_at !== null
            && $order->delivered_at->greaterThanOrEqualTo(now()->subDays((int) config('reviews.window_days')));
    }
}
