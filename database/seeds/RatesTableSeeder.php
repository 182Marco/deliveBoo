<?php

use Illuminate\Database\Seeder;
use App\Rate;
use App\Restaurant;
use Illuminate\Support\Facades\DB;

class RatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // data: restaurant = restaurant name
        $rates =[
            // o'Vesuvio
            [
                "restaurant" => "'O vesuvio",
                "rate" => "1",
            ],
            [
                "restaurant" => "'O vesuvio",
                "rate" => "3",
            ],
            [
                "restaurant" => "'O vesuvio",
                "rate" => "5",
            ],
            // Casa Guadalajara
            [
                "restaurant" => "Casa Guadalajara",
                "rate" => "3",
            ],
            [
                "restaurant" => "Casa Guadalajara",
                "rate" => "3",
            ],
            [
                "restaurant" => "Casa Guadalajara",
                "rate" => "5",
            ],
            [
                "restaurant" => "Casa Guadalajara",
                "rate" => "5",
            ],
              // Hyack
            [
                "restaurant" => "Hyack",
                "rate" => "4",
            ],
            [
                "restaurant" => "Hyack",
                "rate" => "3",
            ],
            [
                "restaurant" => "Hyack",
                "rate" => "3",
            ], 
            [
                "restaurant" => "Hyack",
                "rate" => "5",
            ], 
             // thai food
            [
                "restaurant" => "thai food",
                "rate" => "2",
            ],  
            [
                "restaurant" => "thai food",
                "rate" => "1",
            ],  
            [
                "restaurant" => "thai food",
                "rate" => "2",
            ],  
            // Il cacciatore
            [
                "restaurant" => "Il cacciatore",
                "rate" => "2",
            ], 
            [
                "restaurant" => "Il cacciatore",
                "rate" => "2",
            ], 
            [
                "restaurant" => "Il cacciatore",
                "rate" => "2",
            ], 
            // Il Peri's
            [
                "restaurant" => "Peri's",
                "rate" => "4",
            ], 
            [
                "restaurant" => "Peri's",
                "rate" => "4",
            ], 
            [
                "restaurant" => "Peri's",
                "rate" => "4",
            ], 
            // La cabrera
            [
                "restaurant" => "La cabrera",
                "rate" => "1",
            ], 
            [
                "restaurant" => "La cabrera",
                "rate" => "2",
            ], 
            [
                "restaurant" => "La cabrera",
                "rate" => "1",
            ], 
            // the garage
            [
                "restaurant" => "the garage",
                "rate" => "4",
            ], 
            [
                "restaurant" => "the garage",
                "rate" => "3",
            ], 
            [
                "restaurant" => "the garage",
                "rate" => "4",
            ], 
            [
                "restaurant" => "the garage",
                "rate" => "1",
            ], 
            [
                "restaurant" => "the garage",
                "rate" => "1",
            ], 
            // la-lanterna
            [
                "restaurant" => "La lanterna",
                "rate" => "3",
            ], 
            [
                "restaurant" => "La lanterna",
                "rate" => "3",
            ], 
            [
                "restaurant" => "La lanterna",
                "rate" => "5",
            ], 
            [
                "restaurant" => "La lanterna",
                "rate" => "2",
            ], 
            // green-food
            [
                "restaurant" => "Green food",
                "rate" => "5",
            ], 
            [
                "restaurant" => "Green food",
                "rate" => "5",
            ], 
            [
                "restaurant" => "Green food",
                "rate" => "5",
            ], 
            // la-gondola
            [
                "restaurant" => "La gondola",
                "rate" => "4",
            ], 
            [
                "restaurant" => "La gondola",
                "rate" => "5",
            ], 
            [
                "restaurant" => "La gondola",
                "rate" => "4",
            ], 
            // il-rancio
            [
                "restaurant" => "Il Rancio",
                "rate" => "2",
            ], 
            [
                "restaurant" => "Il Rancio",
                "rate" => "2",
            ], 
            [
                "restaurant" => "Il Rancio",
                "rate" => "4",
            ], 
            // Burger King
            [
                "restaurant" => "Burger King",
                "rate" => "3",
            ], 
            [
                "restaurant" => "Burger King",
                "rate" => "4",
            ], 
            [
                "restaurant" => "Burger King",
                "rate" => "2",
            ], 
            [
                "restaurant" => "Burger King",
                "rate" => "1",
            ], 
            // Mc Donald's
            [
                "restaurant" => "Mc Donald's",
                "rate" => "1",
            ], 
            [
                "restaurant" => "Mc Donald's",
                "rate" => "1",
            ], 
            [
                "restaurant" => "Mc Donald's",
                "rate" => "2",
            ], 
            // Starbucks
            [
                "restaurant" => "Starbucks",
                "rate" => "3",
            ], 
            [
                "restaurant" => "Starbucks",
                "rate" => "3",
            ], 
            [
                "restaurant" => "Starbucks",
                "rate" => "4",
            ], 
            // Esencia
            [
                "restaurant" => "Esencia",
                "rate" => "4",
            ], 
            [
                "restaurant" => "Esencia",
                "rate" => "3",
            ], 
            [
                "restaurant" => "Esencia",
                "rate" => "1",
            ],       
            [
                "restaurant" => "Esencia",
                "rate" => "4",
            ],       
        ];

        // rates have no natural key (the same vote can appear many times),
        // so the seeder "tops up": for every (restaurant, vote) it only creates
        // the rows still missing to reach the number listed above.
        // Running it again creates nothing; existing rates are never deleted.
        $expected = [];
        foreach($rates as $rate){
            $key = $rate['restaurant'] . '|' . $rate['rate'];
            $expected[$key] = ($expected[$key] ?? 0) + 1;
        }

        DB::transaction(function () use ($expected) {
            foreach($expected as $key => $count){
                [$restaurant_name, $vote] = explode('|', $key);
                $restaurant = Restaurant::where('name', $restaurant_name)->first();
                if (!$restaurant) {
                    throw new RuntimeException("RatesTableSeeder: restaurant {$restaurant_name} not found, run RestaurantsTableSeeder first.");
                }

                $existing = Rate::where('restaurant_id', $restaurant->id)->where('rate', $vote)->count();
                for ($i = $existing; $i < $count; $i++) {
                    $new_rate = new Rate();
                    $new_rate->restaurant_id = $restaurant->id;
                    $new_rate->rate = $vote;
                    $new_rate->save();
                }
            }
        });
    }
}
