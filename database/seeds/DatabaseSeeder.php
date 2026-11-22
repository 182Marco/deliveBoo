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
        // "php artisan db:seed" runs all the seeders listed here.
        // REMEMBER! They must be ordered as a cascade: first the tables
        // that give data to the others (users -> types -> restaurants -> plates -> orders / rates).
        // Every seeder is idempotent: it finds records by natural key (email, name, ...)
        // and only creates what is missing, so it can be run again safely.
        $this->call([
            UsersTableSeeder::class,
            TypesTableSeeder::class,
            RestaurantsTableSeeder::class,
            PlatesTableSeeder::class,
            OrdersTableSeeder::class,
            RatesTableSeeder::class,
        ]);
    }
}
