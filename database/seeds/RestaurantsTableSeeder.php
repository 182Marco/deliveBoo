<?php

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
                "img" => "https://media-cdn.tripadvisor.com/media/photo-s/17/c3/64/82/restaurant-front-door.jpg",
            ],
            [
                "owner" => "jhonatansmith@fastweb.it",
                "name" => "Casa Guadalajara",
                "types" => ["Mexican"],
                "phone" => "333 5678987",
                "city" => "Alessandria",
                "address" => "via dei Mille 28",
                "img" => "https://popmenucloud.com/cdn-cgi/image/width=600,height=600,fit=scale-down,format=auto,quality=60/ozmusenq/00968011-16af-4a92-a1de-fe136816913a.jpg",
            ],
            [
                "owner" => "SebastianPalmioli@gmail.com",
                "name" => "Hyack",
                "types" => ["Giapponese"],
                "phone" => "333 7893098",
                "city" => "Milano",
                "address" => "via Manzoni 87",
                "img" => "https://farm5.staticflickr.com/4526/25012994008_7cd8dba11b_z.jpg",
            ],
            [
                "owner" => "carlo.moretti@yahoo.com",
                "name" => "thai food",
                "types" => ["Ethnic"],
                "phone" => "343 9634675",
                "city" => "Milano",
                "address" => "via Parini 42",
                "img" => "https://media-cdn.tripadvisor.com/media/photo-s/06/36/1a/cd/benja-thai-restaurant.jpg",
            ],
            [
                "owner" => "domenico.politi@gmail.com",
                "name" => "Il cacciatore",
                "types" => ["Starred Restaurant"],
                "phone" => "329 6992873",
                "city" => "Pavia",
                "address" => "via Hack 55",
                "img" => "https://i.pinimg.com/originals/fc/a9/c9/fca9c9c1b611fd941c59b7ac08ea6b3f.jpg",
            ],
            [
                "owner" => "alan.andre@gmail.com",
                "name" => "Peri's",
                "types" => ["Starred Restaurant"],
                "phone" => "329 0930876",
                "city" => "Civitanova Marche",
                "address" => "via Vittorio Veneto 55",
                "img" => "https://lh3.googleusercontent.com/NIySnawibm2wTtO1jTQPAzbnbqf9Hha0eoUllVrW_65NKqosO3xqs5t_xMC1_IQ3HsDM1K0w=w1080-h608-p-no-v0",
            ],
            [
                "owner" => "thomas_ercolani@fastweb.com",
                "name" => "La cabrera",
                "types" => ["Ethnic", "Fast Food"],
                "phone" => "366 8656889",
                "city" => "Milano",
                "address" => "via Alighieri 35",
                "img" => "https://i.pinimg.com/originals/08/28/55/08285538fb9906ac4f164b2a2cbbce7b.jpg",
            ],
            [
                "owner" => "federico_romiti@gmail.com",
                "name" => "the garage",
                "types" => ["Pizzeria", "Fast Food"],
                "phone" => "333 5683903",
                "city" => "Piacenza",
                "address" => "via Borromeo 12",
                "img" => "https://www.gdugaragedoors.com/wp-content/uploads/2018/01/Glass-garage-door-straight-on-shot-web.jpg",
            ],
            [
                "owner" => "CristinaBos@hotmail.it",
                "name" => "La lanterna",
                "types" => ["Starred Restaurant"],
                "phone" => "329 8356728",
                "city" => "Cagliari",
                "address" => "via Foscolo 9",
                "img" => "https://www.finedininglovers.it/sites/g/files/xknfdk1106/files/styles/open_graph_image/public/fdl_content_import_it/ristoranti-all-aperto-roma.jpg?itok=hy8ekCZt",
            ],
            [
                "owner" => "Jenny.Pozzato@gmail.com",
                "name" => "Green food",
                "types" => ["Vegan"],
                "phone" => "333 7782395",
                "city" => "Roma",
                "address" => "via Giolitti 34",
                "img" => "https://i.pinimg.com/originals/41/10/25/41102596b0c65a4b98d4f48527a38601.jpg",
            ],
            [
                "owner" => "Ivana_Stefanini@gmail.com",
                "name" => "La gondola",
                "types" => ["Starred Restaurant", "Italian"],
                "phone" => "327 4782401",
                "city" => "Venezia",
                "address" => "via Moro 5",
                "img" => "https://www.italymagazine.com/sites/default/files/styles/800xauto/public/feature-story/leader/bigstock-small-restaurant-on-venetian-c-18389942.jpg?itok=3Vk9SfBm",
            ],
            [
                "owner" => "LauraL@yahoo.it",
                "name" => "Il Rancio",
                "types" => ["Starred Restaurant"],
                "phone" => "347 7359012",
                "city" => "Perugia",
                "address" => "via Salvemini 22",
                "img" => "https://media-cdn.tripadvisor.com/media/photo-s/07/16/3f/60/albergo-ristorante-el.jpg",
            ],
            [
                "owner" => "federico_romiti@gmail.com",
                "name" => "Burger King",
                "types" => ["Fast Food"],
                "phone" => "347 4587567",
                "city" => "Rimini",
                "address" => "via Dalla Chiesa 52",
                "img" => "https://media-cdn.tripadvisor.com/media/photo-s/0c/d5/7b/e2/gillette-burger-king.jpg",
            ],
            [
                "owner" => "federico_romiti@gmail.com",
                "name" => "Mc Donald's",
                "types" => ["Fast Food"],
                "phone" => "333 872391",
                "city" => "Modena",
                "address" => "via Moro 52",
                "img" => "https://thumbs.dreamstime.com/b/front-mcdonalds-mcdonald-golden-arches-shop-sign-logo-128391053.jpg",
            ],
            [
                "owner" => "thomas_ercolani@fastweb.com",
                "name" => "Starbucks",
                "types" => ["Fast Food"],
                "phone" => "347 552997",
                "city" => "Milano",
                "address" => "via De Gasperi 52",
                "img" => "https://i.pinimg.com/originals/31/35/53/313553bc8b3f4bbf9c1b3dc8bed1b4dd.jpg",
            ],
            [
                "owner" => "thomas_ercolani@fastweb.com",
                "name" => "Esencia",
                "types" => ["Spanish"],
                "phone" => "333 3578540",
                "city" => "Milano",
                "address" => "via Fransisco Goya 52",
                "img" => "https://live.staticflickr.com/4832/45987052051_d7341d60e6_b.jpg",
            ],
        ];

        // idempotent: restaurants are identified by their name (UNIQUE),
        // the owner by the user email and the types by their name: no autoincrement ids.
        DB::transaction(function () use ($restaurants) {
            foreach($restaurants as $restaurant){
                $owner = User::where('email', $restaurant['owner'])->first();
                if (!$owner) {
                    throw new RuntimeException("RestaurantsTableSeeder: user {$restaurant['owner']} not found, run UsersTableSeeder first.");
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
                    throw new RuntimeException("RestaurantsTableSeeder: missing types for {$restaurant['name']}, run TypesTableSeeder first.");
                }
                $new_restaurant->types()->syncWithoutDetaching($type_ids->all());
            }
        });
    }
}
