<?php

namespace Database\Seeders;

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
        // "php artisan db:seed" runs all the seeders listed here.
        // REMEMBER! They must be ordered as a cascade: first the tables
        // that give data to the others (reference data -> users -> types -> restaurants -> plates -> orders / rates).
        // Every seeder is idempotent: it finds records by natural key (email, name, ...)
        // and only creates what is missing, so it can be run again safely.
        $this->call([
            // reference data
            AllergensSeeder::class,
            DietaryTagsSeeder::class,
            IngredientsSeeder::class,
            // historic hand-written data
            UsersTableSeeder::class,
            TypesTableSeeder::class,
            RestaurantsTableSeeder::class,
            PlatesTableSeeder::class,
            LegacyPlateDetailsSeeder::class,
            // generated catalog (size: config/seeding.php, SEED_PROFILE)
            OwnersSeeder::class,
            CatalogRestaurantsSeeder::class,
            MenusSeeder::class,
            // commerce: historic, then generated orders and rates
            OrdersTableSeeder::class,
            OrderHistorySeeder::class,
            RatesTableSeeder::class,
            RatingsSeeder::class,
        ]);
    }
}
