<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Final addresses of the demo accounts shown on the login page (config/demo.php): named after the
// role, not the person. The earlier 2026_11_24_010000 migration moved Federico off gmail first.
return new class extends Migration
{
    private const MOVES = [
        'federico.romiti@foodsy.test' => 'restaurant_owner@foodsy.test',
        'mario.rossi@foodsy.test' => 'customer@foodsy.test',
    ];

    public function up(): void
    {
        foreach (self::MOVES as $old => $new) {
            self::move($old, $new);
        }
    }

    public function down(): void
    {
        foreach (self::MOVES as $old => $new) {
            self::move($new, $old);
        }
    }

    // idempotent: nothing to do on a fresh database or when the target address is taken
    private static function move(string $from, string $to): void
    {
        if (DB::table('users')->where('email', $to)->exists()) {
            return;
        }
        DB::table('users')->where('email', $from)->update(['email' => $to]);
        DB::table('password_resets')->where('email', $from)->delete();
    }
};
