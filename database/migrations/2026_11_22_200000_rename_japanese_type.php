<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// the product is English only: the cuisine seeded with its Italian name becomes "Japanese"
return new class extends Migration
{
    private const LEGACY_NAME = 'Giapponese';

    public function up(): void
    {
        DB::table('types')->where('name', self::LEGACY_NAME)->update(['name' => 'Japanese']);
    }

    public function down(): void
    {
        DB::table('types')->where('name', 'Japanese')->update(['name' => self::LEGACY_NAME]);
    }
};
