<?php

use Illuminate\Database\Seeder;
use App\Restaurant;
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
        // // data
        $restaurants =[
            [
                "user_id" => "2",
                "name" => "'O vesuvio",
                "phone" => "329 5868797",
                "city" => "Milano",
                "address" => "via dei Pini 7",
                "img" => "https://media-cdn.tripadvisor.com/media/photo-s/17/c3/64/82/restaurant-front-door.jpg",
            ],
            [
                "user_id" => "4",
                "name" => "Casa Guadalajara",
                "phone" => "333 5678987",
                "city" => "Alessandria",
                "address" => "via dei Mille 28",
                "img" => "https://popmenucloud.com/cdn-cgi/image/width=600,height=600,fit=scale-down,format=auto,quality=60/ozmusenq/00968011-16af-4a92-a1de-fe136816913a.jpg",
            ],
            [
                "user_id" => "5",
                "name" => "Hyack",
                "phone" => "333 7893098",
                "city" => "Milano",
                "address" => "via Manzoni 87",
                "img" => "https://farm5.staticflickr.com/4526/25012994008_7cd8dba11b_z.jpg",
            ],
            [
                "user_id" => "3",
                "name" => "thai food",
                "phone" => "343 9634675",
                "city" => "Milano",
                "address" => "via Parini 42",
                "img" => "https://media-cdn.tripadvisor.com/media/photo-s/06/36/1a/cd/benja-thai-restaurant.jpg",
            ],
            [
                "user_id" => "1",
                "name" => "Il cacciatore",
                "phone" => "329 6992873",
                "city" => "Pavia",
                "address" => "via Hack 55",
                "img" => "https://i.pinimg.com/originals/fc/a9/c9/fca9c9c1b611fd941c59b7ac08ea6b3f.jpg",
            ],
            [
                "user_id" => "12",
                "name" => "Peri's",
                "phone" => "329 0930876",
                "city" => "Civitanova Marche",
                "address" => "via Vittorio Veneto 55",
                "img" => "https://lh3.googleusercontent.com/NIySnawibm2wTtO1jTQPAzbnbqf9Hha0eoUllVrW_65NKqosO3xqs5t_xMC1_IQ3HsDM1K0w=w1080-h608-p-no-v0",
            ],
            [
                "user_id" => "6",
                "name" => "La cabrera",
                "phone" => "366 8656889",
                "city" => "Milano",
                "address" => "via Alighieri 35",
                "img" => "https://i.pinimg.com/originals/08/28/55/08285538fb9906ac4f164b2a2cbbce7b.jpg",
            ],
            [
                "user_id" => "9",
                "name" => "the garage",
                "phone" => "333 5683903",
                "city" => "Piacenza",
                "address" => "via Borromeo 12",
                "img" => "https://www.gdugaragedoors.com/wp-content/uploads/2018/01/Glass-garage-door-straight-on-shot-web.jpg",
            ],
            [
                "user_id" => "10",
                "name" => "La lanterna",
                "phone" => "329 8356728",
                "city" => "Cagliari",
                "address" => "via Foscolo 9",
                "img" => "https://www.finedininglovers.it/sites/g/files/xknfdk1106/files/styles/open_graph_image/public/fdl_content_import_it/ristoranti-all-aperto-roma.jpg?itok=hy8ekCZt",
            ],
            [
                "user_id" => "7",
                "name" => "Green food",
                "phone" => "333 7782395",
                "city" => "Roma",
                "address" => "via Giolitti 34",
                "img" => "https://i.pinimg.com/originals/41/10/25/41102596b0c65a4b98d4f48527a38601.jpg",
            ],
            [
                "user_id" => "11",
                "name" => "La gondola",
                "phone" => "327 4782401",
                "city" => "Venezia",
                "address" => "via Moro 5",
                "img" => "https://www.italymagazine.com/sites/default/files/styles/800xauto/public/feature-story/leader/bigstock-small-restaurant-on-venetian-c-18389942.jpg?itok=3Vk9SfBm",
            ],
            [
                "user_id" => "8",
                "name" => "Il Rancio",
                "phone" => "347 7359012",
                "city" => "Perugia",
                "address" => "via Salvemini 22",
                "img" => "https://media-cdn.tripadvisor.com/media/photo-s/07/16/3f/60/albergo-ristorante-el.jpg",
            ],
            [
                "user_id" => "9",
                "name" => "Burger King",
                "phone" => "347 4587567",
                "city" => "Rimini",
                "address" => "via Dalla Chiesa 52",
                "img" => "https://media-cdn.tripadvisor.com/media/photo-s/0c/d5/7b/e2/gillette-burger-king.jpg",
            ],
            [
                "user_id" => "9",
                "name" => "Mc Donald's",
                "phone" => "333 872391",
                "city" => "Modena",
                "address" => "via Moro 52",
                "img" => "https://thumbs.dreamstime.com/b/front-mcdonalds-mcdonald-golden-arches-shop-sign-logo-128391053.jpg",
            ],
            [
                "user_id" => "6",
                "name" => "Starbucks",
                "phone" => "347 552997",
                "city" => "Milano",
                "address" => "via De Gasperi 52",
                "img" => "https://i.pinimg.com/originals/31/35/53/313553bc8b3f4bbf9c1b3dc8bed1b4dd.jpg",
            ],
            [
                "user_id" => "6",
                "name" => "Esencia",
                "phone" => "333 3578540",
                "city" => "Milano",
                "address" => "via Fransisco Goya 52",
                "img" => "https://live.staticflickr.com/4832/45987052051_d7341d60e6_b.jpg",
            ],
        ];

        foreach($restaurants as $restaurant){
            // instance
            $new_restaurant = new Restaurant();
            // populate
            $new_restaurant->user_id = $restaurant['user_id'];
            $new_restaurant->name = $restaurant['name'];
            $new_restaurant->slug = Str::slug( $new_restaurant->name, '-');
            $new_restaurant->phone = $restaurant['phone'];
            $new_restaurant->city = $restaurant['city'];
            $new_restaurant->address = $restaurant['address'];
            $new_restaurant->img = $restaurant['img'];
            // save
            $new_restaurant->save();
        } 
        
        
        
        //    seedind the many to many with types

           $vesuvio = Restaurant::find(1);
           $vesuvio ->types()->attach(6); //pizzeria

           $Guadalajara= Restaurant::find(2);
           $Guadalajara->types()->attach(1); //pizzeria

           $hayack = Restaurant::find(3);
           $hayack ->types()->attach(3); //giapponese

           $thai_food = Restaurant::find(4);
           $thai_food ->types()->attach(7);  // ethnic

           $il_cacciatore = Restaurant::find(5);
           $il_cacciatore ->types()->attach(4); //stellato

           $IPeri = Restaurant::find(6);
           $IPeri ->types()->attach(4); //stellato

           $cabrera = Restaurant::find(7);
           $cabrera ->types()->sync([7, 5]); //ethnic //fast food

           $the_garage = Restaurant::find(8);
           $the_garage ->types()->sync([6, 5]); //pizzeria // fast food

           $La_lanterna = Restaurant::find(9);
           $La_lanterna ->types()->attach(4); //stellato

           $Green_food = Restaurant::find(10);
           $Green_food ->types()->sync([2]); //vegano

           $La_gondola = Restaurant::find(11);
           $La_gondola ->types()->sync([4,8]); //stellato //italiano

           $Il_Rancio = Restaurant::find(12);
           $Il_Rancio ->types()->attach(4); //stellato  

           $Burger_King = Restaurant::find(13);
           $Burger_King ->types()->attach(5); //fast food  

           $Mc_Donald = Restaurant::find(14);
           $Mc_Donald ->types()->attach(5); //fast food   

           $StarBucks = Restaurant::find(15);
           $StarBucks ->types()->attach(5); //fast food   

           $Esencia = Restaurant::find(16);
           $Esencia ->types()->attach(9); //Spanish      
    }
}
