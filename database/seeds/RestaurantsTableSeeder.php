<?php

use Illuminate\Database\Seeder;
use App\Restaurant;

class RestaurantsTableSeede extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // New instance
        $new_restaurant = new Restaurant();

        $new_restaurant->restaurant_name = 'Benvenuti al sud';
        $new_restaurant->restaurant_phone = '3357694838';
        $new_restaurant->restaurant_address = 'via Rossi, 6';
        $new_restaurant->vat_number = '35462748563';
        $new_restaurant->restaurant_img = 'https://picsum.photos/200/300';

        $new_restaurant->save();
    }
}
