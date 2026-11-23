<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Data only: the demo restaurateur (Federico Romiti) gets a password in line with the other demo
// accounts (config/demo.php, `foodsy-owner` by default). The seeders never overwrite an existing
// account, so a database seeded before needs this. Demo accounts cannot change their password from
// the interface, so nothing personal is lost. Idempotent; nothing to undo.
return new class extends Migration
{
    public function up(): void
    {
        $email = config('demo.accounts.restaurateur.email');
        $password = config('demo.accounts.restaurateur.password');
        if (! $email || ! $password) {
            return;
        }

        DB::table('users')->where('email', $email)->update(['password' => Hash::make($password)]);
    }

    public function down(): void
    {
        // the previous password is not restored
    }
};
