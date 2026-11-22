<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Prices as exact DECIMAL on MySQL (replaces the DOUBLE of 2026_11_22_000000).
// Eloquent reads DECIMAL as a string: Plate and Order cast price to float, so the JSON
// stays numeric and the cart keeps summing numbers (see AGENTS.md, "Trappole").
// SQLite (tests) has no DECIMAL storage class: nothing to do there.
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE plates MODIFY price DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE orders MODIFY price DECIMAL(10,2) NOT NULL');
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE plates MODIFY price DOUBLE(5,2) NOT NULL');
        DB::statement('ALTER TABLE orders MODIFY price DOUBLE(8,2) NOT NULL');
    }
};
