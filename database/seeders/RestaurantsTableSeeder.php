<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Restaurant;
use App\Type;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestaurantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // data: owner = user email, types = type names
        $restaurants =[
            [
                "owner" => "italo_maroni@hotmail.it",
                "name" => "'O vesuvio",
                "types" => ["Pizzeria"],
                "phone" => "329 5868797",
                "city" => "Milano",
                "address" => "via dei Pini 7",
                "img" => "https://images.unsplash.com/photo-1590947132387-155cc02f3212?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "jhonatansmith@fastweb.it",
                "name" => "Casa Guadalajara",
                "types" => ["Mexican"],
                "phone" => "333 5678987",
                "city" => "Alessandria",
                "address" => "via dei Mille 28",
                "img" => "https://images.unsplash.com/photo-1564767655658-4e6b365884ff?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "SebastianPalmioli@gmail.com",
                "name" => "Hyack",
                "types" => ["Japanese"],
                "phone" => "333 7893098",
                "city" => "Milano",
                "address" => "via Manzoni 87",
                "img" => "https://images.unsplash.com/photo-1611143669185-af224c5e3252?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "carlo.moretti@yahoo.com",
                "name" => "thai food",
                "types" => ["Ethnic"],
                "phone" => "343 9634675",
                "city" => "Milano",
                "address" => "via Parini 42",
                "img" => "https://images.unsplash.com/photo-1562565652-a0d8f0c59eb4?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "domenico.politi@gmail.com",
                "name" => "Il cacciatore",
                "types" => ["Starred Restaurant"],
                "phone" => "329 6992873",
                "city" => "Pavia",
                "address" => "via Hack 55",
                "img" => "https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "alan.andre@gmail.com",
                "name" => "Peri's",
                "types" => ["Starred Restaurant"],
                "phone" => "329 0930876",
                "city" => "Civitanova Marche",
                "address" => "via Vittorio Veneto 55",
                "img" => "https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "thomas_ercolani@fastweb.com",
                "name" => "La cabrera",
                "types" => ["Ethnic", "Fast Food"],
                "phone" => "366 8656889",
                "city" => "Milano",
                "address" => "via Alighieri 35",
                "img" => "https://images.unsplash.com/photo-1546964124-0cce460f38ef?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "federico_romiti@gmail.com",
                "name" => "the garage",
                "types" => ["Pizzeria", "Fast Food"],
                "phone" => "333 5683903",
                "city" => "Piacenza",
                "address" => "via Borromeo 12",
                "img" => "https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "CristinaBos@hotmail.it",
                "name" => "La lanterna",
                "types" => ["Starred Restaurant"],
                "phone" => "329 8356728",
                "city" => "Cagliari",
                "address" => "via Foscolo 9",
                "img" => "https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "Jenny.Pozzato@gmail.com",
                "name" => "Green food",
                "types" => ["Vegan"],
                "phone" => "333 7782395",
                "city" => "Roma",
                "address" => "via Giolitti 34",
                "img" => "https://images.unsplash.com/photo-1600335895229-6e75511892c8?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "Ivana_Stefanini@gmail.com",
                "name" => "La gondola",
                "types" => ["Starred Restaurant", "Italian"],
                "phone" => "327 4782401",
                "city" => "Venezia",
                "address" => "via Moro 5",
                "img" => "https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "LauraL@yahoo.it",
                "name" => "Il Rancio",
                "types" => ["Starred Restaurant"],
                "phone" => "347 7359012",
                "city" => "Perugia",
                "address" => "via Salvemini 22",
                "img" => "https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "federico_romiti@gmail.com",
                "name" => "Burger King",
                "types" => ["Fast Food"],
                "phone" => "347 4587567",
                "city" => "Rimini",
                "address" => "via Dalla Chiesa 52",
                "img" => "https://images.unsplash.com/photo-1602030638412-bb8dcc0bc8b0?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "federico_romiti@gmail.com",
                "name" => "Mc Donald's",
                "types" => ["Fast Food"],
                "phone" => "333 872391",
                "city" => "Modena",
                "address" => "via Moro 52",
                "img" => "https://images.unsplash.com/photo-1551782450-a2132b4ba21d?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "thomas_ercolani@fastweb.com",
                "name" => "Starbucks",
                "types" => ["Fast Food"],
                "phone" => "347 552997",
                "city" => "Milano",
                "address" => "via De Gasperi 52",
                "img" => "https://images.unsplash.com/photo-1453614512568-c4024d13c247?auto=format&fit=crop&w=1600&q=75",
            ],
            [
                "owner" => "thomas_ercolani@fastweb.com",
                "name" => "Esencia",
                "types" => ["Spanish"],
                "phone" => "333 3578540",
                "city" => "Milano",
                "address" => "via Fransisco Goya 52",
                "img" => "https://images.unsplash.com/photo-1630175860333-5131bda75071?auto=format&fit=crop&w=1600&q=75",
            ],
        ];

        // idempotent: restaurants are identified by their name (UNIQUE),
        // the owner by the user email and the types by their name: no autoincrement ids.
        DB::transaction(function () use ($restaurants) {
            foreach($restaurants as $restaurant){
                $owner = User::where('email', $restaurant['owner'])->first();
                if (!$owner) {
                    throw new \RuntimeException("RestaurantsTableSeeder: user {$restaurant['owner']} not found, run UsersTableSeeder first.");
                }

                // instance (existing or new)
                $new_restaurant = Restaurant::firstOrNew(['name' => $restaurant['name']]);
                if (!$new_restaurant->exists) {
                    // populate
                    $new_restaurant->user_id = $owner->id;
                    $new_restaurant->slug = Str::slug( $new_restaurant->name, '-');
                    $new_restaurant->phone = $restaurant['phone'];
                    $new_restaurant->city = $restaurant['city'];
                    $new_restaurant->address = $restaurant['address'];
                    $new_restaurant->img = $restaurant['img'];
                    // save
                    $new_restaurant->save();
                }

                // seeding the many to many with types (never duplicates, never detaches)
                $type_ids = Type::whereIn('name', $restaurant['types'])->pluck('id');
                if (count($type_ids) !== count($restaurant['types'])) {
                    throw new \RuntimeException("RestaurantsTableSeeder: missing types for {$restaurant['name']}, run TypesTableSeeder first.");
                }
                $new_restaurant->types()->syncWithoutDetaching($type_ids->all());
            }
        });
    }
}
