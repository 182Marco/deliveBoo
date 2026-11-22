<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Type;
use Illuminate\Support\Facades\DB;

class TypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $types =[
            [
                "name" => "Mexican",
                "img" => "https://images.unsplash.com/photo-1504544750208-dc0358e63f7f?auto=format&fit=crop&w=600&q=75",
            ],
            [
                "name" => "Vegan",
                "img" => "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=600&q=75",
            ],
            [
                "name" => "Japanese",
                "img" => "https://images.unsplash.com/photo-1553621042-f6e147245754?auto=format&fit=crop&w=600&q=75",
            ],
            [
                "name" => "Starred Restaurant",
                "img" => "https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=600&q=75",
            ],
            [
                "name" => "Fast Food",
                "img" => "https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5?auto=format&fit=crop&w=600&q=75",
            ],
            [
                "name" => "Pizzeria",
                "img" => "https://images.unsplash.com/photo-1604382354936-07c5d9983bd3?auto=format&fit=crop&w=600&q=75",
            ],
            [
                "name" => "Ethnic",
                "img" => "https://images.unsplash.com/photo-1569562211093-4ed0d0758f12?auto=format&fit=crop&w=600&q=75",
            ],
            [
                "name" => "Italian",
                "img" => "https://images.unsplash.com/photo-1622973536968-3ead9e780960?auto=format&fit=crop&w=600&q=75",
            ],
            [
                "name" => "Spanish",
                "img" => "https://images.unsplash.com/photo-1515443961218-a51367888e4b?auto=format&fit=crop&w=600&q=75",
            ],
        ];



        // idempotent: types are identified by their name.
        DB::transaction(function () use ($types) {
            foreach($types as $type){
                $new_type = Type::firstOrNew(['name' => $type['name']]);
                if ($new_type->exists) {
                    continue;
                }
                $new_type->img = $type['img'];
                $new_type->save();
            }
        });
    }
}
