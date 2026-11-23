<?php

namespace App\Policies;

use App\Plate;
use App\User;

// a plate belongs to whoever owns its restaurant
class PlatePolicy
{
    public function manage(User $user, Plate $plate): bool
    {
        $restaurant = $plate->restaurant;

        return $restaurant !== null && $user->can('manage', $restaurant);
    }
}
