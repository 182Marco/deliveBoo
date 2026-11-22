<?php

namespace Database\Seeders;

use App\DietaryTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// reference data: dietary characteristics (data/dietary_tags.php). Idempotent by code.
class DietaryTagsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (require __DIR__ . '/data/dietary_tags.php' as $code => [$name, $kind, $description]) {
                DietaryTag::firstOrCreate(['code' => $code], ['name' => $name, 'kind' => $kind, 'description' => $description]);
            }
        });
    }
}
