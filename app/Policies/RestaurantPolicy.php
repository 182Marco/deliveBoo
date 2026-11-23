<?php

namespace App\Policies;

use App\Restaurant;
use App\User;

// partner hub: a restaurateur works only on his own restaurants (and their plates, orders, stats)
class RestaurantPolicy
{
    public function manage(User $user, Restaurant $restaurant): bool
    {
        return $restaurant->user_id !== null && (int) $restaurant->user_id === (int) $user->id;
    }
}
