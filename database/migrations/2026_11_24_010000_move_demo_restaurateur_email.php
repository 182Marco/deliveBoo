<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The demo restaurateur (Federico Romiti) is shown on the public login page: his historic seed
// address used a real mail domain, so it moves to the reserved .test domain (RFC 2606).
// Restaurants follow by user_id; orders and rates are not tied to the email.
return new class extends Migration
{
    private const OLD = 'federico_romiti@gmail.com';
    private const NEW = 'federico.romiti@foodsy.test';

    public function up(): void
    {
        // idempotent: nothing to do on a fresh database or if the new address is already taken
        if (DB::table('users')->where('email', self::NEW)->exists()) {
            return;
        }
        DB::table('users')->where('email', self::OLD)->update(['email' => self::NEW]);
        DB::table('password_resets')->where('email', self::OLD)->delete();
    }

    public function down(): void
    {
        if (DB::table('users')->where('email', self::OLD)->exists()) {
            return;
        }
        DB::table('users')->where('email', self::NEW)->update(['email' => self::OLD]);
    }
};
