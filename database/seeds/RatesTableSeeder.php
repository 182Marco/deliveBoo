<?php

use Illuminate\Database\Seeder;
use App\Rate;

class RatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $rates =[
            // id 1 o'Vesuvio
            [
                "restaurant_id" => "1",
                "rate" => "1",
            ],
            [
                "restaurant_id" => "1",
                "rate" => "3",
            ],
            [
                "restaurant_id" => "1",
                "rate" => "5",
            ],
            // id 2 Casa Guadalajara
            [
                "restaurant_id" => "2",
                "rate" => "3",
            ],
            [
                "restaurant_id" => "2",
                "rate" => "3",
            ],
            [
                "restaurant_id" => "2",
                "rate" => "5",
            ],
            [
                "restaurant_id" => "2",
                "rate" => "5",
            ],
              // id 3 Hyack
            [
                "restaurant_id" => "3",
                "rate" => "4",
            ],
            [
                "restaurant_id" => "3",
                "rate" => "3",
            ],
            [
                "restaurant_id" => "3",
                "rate" => "3",
            ], 
            [
                "restaurant_id" => "3",
                "rate" => "5",
            ], 
             // id 4 thai food  
            [
                "restaurant_id" => "4",
                "rate" => "2",
            ],  
            [
                "restaurant_id" => "4",
                "rate" => "1",
            ],  
            [
                "restaurant_id" => "4",
                "rate" => "2",
            ],  
            // id 5 Il cacciatore
            [
                "restaurant_id" => "5",
                "rate" => "2",
            ], 
            [
                "restaurant_id" => "5",
                "rate" => "2",
            ], 
            [
                "restaurant_id" => "5",
                "rate" => "2",
            ], 
            // id 6 Il Peri's
            [
                "restaurant_id" => "6",
                "rate" => "4",
            ], 
            [
                "restaurant_id" => "6",
                "rate" => "4",
            ], 
            [
                "restaurant_id" => "6",
                "rate" => "4",
            ], 
            // id 7 La cabrera
            [
                "restaurant_id" => "7",
                "rate" => "1",
            ], 
            [
                "restaurant_id" => "7",
                "rate" => "2",
            ], 
            [
                "restaurant_id" => "7",
                "rate" => "1",
            ], 
            // id 8 the garage
            [
                "restaurant_id" => "8",
                "rate" => "4",
            ], 
            [
                "restaurant_id" => "8",
                "rate" => "3",
            ], 
            [
                "restaurant_id" => "8",
                "rate" => "4",
            ], 
            [
                "restaurant_id" => "8",
                "rate" => "1",
            ], 
            [
                "restaurant_id" => "8",
                "rate" => "1",
            ], 
            // id 9 la-lanterna
            [
                "restaurant_id" => "9",
                "rate" => "3",
            ], 
            [
                "restaurant_id" => "9",
                "rate" => "3",
            ], 
            [
                "restaurant_id" => "9",
                "rate" => "5",
            ], 
            [
                "restaurant_id" => "9",
                "rate" => "2",
            ], 
            // id 10 green-food
            [
                "restaurant_id" => "10",
                "rate" => "5",
            ], 
            [
                "restaurant_id" => "10",
                "rate" => "5",
            ], 
            [
                "restaurant_id" => "10",
                "rate" => "5",
            ], 
            // id 11 la-gondola
            [
                "restaurant_id" => "11",
                "rate" => "4",
            ], 
            [
                "restaurant_id" => "11",
                "rate" => "5",
            ], 
            [
                "restaurant_id" => "11",
                "rate" => "4",
            ], 
            // id 12 il-rancio
            [
                "restaurant_id" => "12",
                "rate" => "2",
            ], 
            [
                "restaurant_id" => "12",
                "rate" => "2",
            ], 
            [
                "restaurant_id" => "12",
                "rate" => "4",
            ], 
            // id 13 Burger King
            [
                "restaurant_id" => "13",
                "rate" => "3",
            ], 
            [
                "restaurant_id" => "13",
                "rate" => "4",
            ], 
            [
                "restaurant_id" => "13",
                "rate" => "2",
            ], 
            [
                "restaurant_id" => "13",
                "rate" => "1",
            ], 
            // id 14 Mc Donald's
            [
                "restaurant_id" => "14",
                "rate" => "1",
            ], 
            [
                "restaurant_id" => "14",
                "rate" => "1",
            ], 
            [
                "restaurant_id" => "14",
                "rate" => "2",
            ], 
            // id 15 Starbucks
            [
                "restaurant_id" => "15",
                "rate" => "3",
            ], 
            [
                "restaurant_id" => "15",
                "rate" => "3",
            ], 
            [
                "restaurant_id" => "15",
                "rate" => "4",
            ], 
            // id 16 Esencia
            [
                "restaurant_id" => "16",
                "rate" => "4",
            ], 
            [
                "restaurant_id" => "16",
                "rate" => "3",
            ], 
            [
                "restaurant_id" => "16",
                "rate" => "1",
            ],       
            [
                "restaurant_id" => "16",
                "rate" => "4",
            ],       
        ];



        foreach($rates as $rate){
            $new_rate = new Rate();
            $new_rate->restaurant_id = $rate['restaurant_id'];
            $new_rate->rate = $rate['rate'];
            $new_rate->save();
        };
    }
}
