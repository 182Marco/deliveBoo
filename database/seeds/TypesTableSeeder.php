<?php

use Illuminate\Database\Seeder;
use App\Type;

use function PHPSTORM_META\type;

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
            // Mc Downalds Plates
            [
                "name" => "Mexican",
                "img" => "https://www.generazionepost.it/wp-content/uploads/2020/01/Cibo-Messicano.jpg",
            ],
            [
                "name" => "Vegan",
                "img" => "https://www.calcionewsweb.it/wp-content/uploads/2019/09/dieta-vegana.jpg",
            ],
            [
                "name" => "Giapponese",
                "img" => "https://www.ilgiornaledelcibo.it/wp-content/uploads/2015/03/cultura-culinaria-giapponese.jpg",
            ],
            [
                "name" => "Starred Restaurant",
                "img" => "https://www.gustorotondo.it/wp-content/uploads/2019/02/Ristoranti-stellati-Veneto-2019-indicazioni.jpg",
            ],
            [
                "name" => "Fast Food",
                "img" => "https://static.ohga.it/wp-content/uploads/sites/24/2019/03/cibo-nei-fast-food-qualit%C3%A0-peggiorata.jpg",
            ],
            [
                "name" => "Pizzeria",
                "img" => "https://www.pizzanapoletana.org/struttura/pagine_bicolor/mobile/disciplinare_avpn_2.jpg",
            ],
            [
                "name" => "Ethnic",
                "img" => "http://www.scienze-naturali.com/wp-content/uploads/2016/09/menu-etnici-taste14-1024x683.jpg",
            ],
            [
                "name" => "Italian",
                "img" => "https://www.foodweb.it/wp-content/uploads/2020/06/cibo-italiano.jpg",
            ],
            [
                "name" => "Spanish",
                "img" => "https://www.viaggi-estate.com/wp-content/uploads/2016/05/spagna-cibo-paella.jpg",
            ],
        ];



        foreach($types as $type){
            $new_type = new Type();
            $new_type->name = $type['name'];
            $new_type->img = $type['img'];
            $new_type->save();
        };
    }
}
