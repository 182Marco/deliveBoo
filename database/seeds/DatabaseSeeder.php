<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        {
            // this is need to run globally "php artisan seed"
            // refferening to all those seeeder
            // which are in this array.
            // REMEMBER!
            // they must be ordered in sequence -> interpretated as a cascade ->
            // first I have to populate those table whitch give data to other tables
            $this->call([
                UsersTableSeeder::class, 
                TypesTableSeeder::class,
                RestaurantsTableSeeder::class,
                PlatesTableSeeder::class, 
            ]);
        }
    }
}
