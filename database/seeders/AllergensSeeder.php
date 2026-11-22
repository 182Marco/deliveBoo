<?php

namespace Database\Seeders;

use App\Allergen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// reference data: the 14 allergens of the EU regulation (data/allergens.php). Idempotent by code.
class AllergensSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (require __DIR__ . '/data/allergens.php' as $code => [$annex, $name, $description]) {
                Allergen::firstOrCreate(['code' => $code], ['annex_number' => $annex, 'name' => $name, 'description' => $description]);
            }
        });
    }
}
