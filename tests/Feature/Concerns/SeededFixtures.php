<?php

namespace Tests\Feature\Concerns;

use App\Order;
use App\Plate;
use App\Restaurant;
use App\User;

/**
 * Shared fixtures on top of the idempotent seeders (natural keys, never ids).
 * owner  = restaurant_owner@foodsy.test, owns "Mc Donald's" (and others)
 * other  = domenico.politi@gmail.com, owns "Il cacciatore", not "Mc Donald's"
 */
trait SeededFixtures
{
    protected function owner()
    {
        return User::where('email', 'restaurant_owner@foodsy.test')->firstOrFail();
    }

    protected function otherUser()
    {
        return User::where('email', 'domenico.politi@gmail.com')->firstOrFail();
    }

    protected function ownedRestaurant()
    {
        return Restaurant::where('name', "Mc Donald's")->firstOrFail();
    }

    protected function otherRestaurant()
    {
        return Restaurant::where('name', 'Il cacciatore')->firstOrFail();
    }

    protected function ownedPlate()
    {
        return Plate::where('restaurant_id', $this->ownedRestaurant()->id)->orderBy('id')->firstOrFail();
    }

    protected function ownedOrder()
    {
        return Order::where('restaurant_id', $this->ownedRestaurant()->id)->orderBy('id')->firstOrFail();
    }
}
