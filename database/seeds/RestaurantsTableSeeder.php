<?php

use Illuminate\Database\Seeder;
use App\Restaurant;

class RestaurantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // data
        $restaurants =[
            [
                "name" => "La_Pizza_Napoli",
                "phone" => "329 5868797",
                "city" => "Milano",
                "address" => "via dei Pini 7",
                "img" => "",
            ],
            [
                "name" => "My mexican pall",
                "phone" => "333 5678987",
                "city" => "Trento",
                "address" => "via dei Mille 28",
                "img" => "",
            ],
            [
                "name" => "Sushi Li",
                "phone" => "333 7893098",
                "city" => "Milano",
                "address" => "via Manzoni 87",
                "img" => "",
            ],
            [
                "name" => "thai food",
                "phone" => "343 9634675",
                "city" => "Milano",
                "address" => "via Parini 42",
                "img" => "",
            ],
            [
                "name" => "Il cacciatore",
                "phone" => "329 6992873",
                "city" => "Pavia",
                "address" => "via Hack 55",
                "img" => "",
            ],
            [
                "name" => "Il gabbiano",
                "phone" => "329 0930876",
                "city" => "Civitanova Marche",
                "address" => "via Vittorio Veneto 55",
                "img" => "",
            ],
            [
                "name" => "El Gaucho",
                "phone" => "366 8656889",
                "city" => "Milano",
                "address" => "via Alighieri 35",
                "img" => "",
            ],
            [
                "name" => "the garage",
                "phone" => "333 5683903",
                "city" => "Piacenza",
                "address" => "via Borromeo 12",
                "img" => "",
            ],
            [
                "name" => "La lanterna",
                "phone" => "329 8356728",
                "city" => "Cagliari",
                "address" => "via Foscolo 9",
                "img" => "",
            ],
            [
                "name" => "Green food",
                "phone" => "333 7782395",
                "city" => "Roma",
                "address" => "via Giolitti 34",
                "img" => "",
            ],
            [
                "name" => "La gondola",
                "phone" => "327 4782401",
                "city" => "Venezia",
                "address" => "via Moro 5",
                "img" => "",
            ],
            [
                "name" => "Il Rancio",
                "phone" => "347 7359012",
                "city" => "Ancona",
                "address" => "via Salvemini 22",
                "img" => "",
            ],
        ];

        foreach($restaurants as $restaurant){
            // instance
            $new_restaurant = new Restaurant();
            // populate
            $new_restaurant->name = $restaurant['name'];
            $new_restaurant->phone = $restaurant['phone'];
            $new_restaurant->city = $restaurant['city'];
            $new_restaurant->address = $restaurant['address'];
            $new_restaurant->img = $restaurant['img'];
            // save
            $new_restaurant->save();
        }    
    }
}
