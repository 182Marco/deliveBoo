<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Laravel 7 created plates.price and orders.price, declared as float('price', 5, 2)
 * and float('price'), as DOUBLE(5,2) and DOUBLE(8,2). Since Laravel 11 the same
 * declarations give a single precision FLOAT and a DOUBLE without scale on MySQL.
 * This restores the original column types, so a fresh database matches the old ones
 * (on a database created by Laravel 7 it changes nothing). SQLite stores REAL anyway.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE plates MODIFY price DOUBLE(5,2) NOT NULL');
        DB::statement('ALTER TABLE orders MODIFY price DOUBLE(8,2) NOT NULL');
    }

    public function down(): void
    {
        // nothing to undo: the original migrations define these columns
    }
};
