<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// The demo accounts of config/demo.php: the platform admin and the customer Mario Rossi are created
// here, the restaurateur (Federico Romiti) comes from UsersTableSeeder and is only checked.
// The only way to get an admin besides `php artisan foodsy:make-admin`. Idempotent by email:
// an existing account is never overwritten (role, password and data changed by hand stay).
class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (config('demo.accounts') as $key => $account) {
                $user = User::firstOrNew(['email' => $account['email']]);
                if ($user->exists) {
                    continue;
                }
                if ($account['role'] === User::RESTAURATEUR) {
                    throw new \RuntimeException("DemoAccountsSeeder: the demo restaurateur {$account['email']} must come from UsersTableSeeder.");
                }
                $user->name = $account['name'];
                $user->last_name = $account['last_name'];
                $user->password = Hash::make($account['password']);
                $user->email_verified_at = now();
                // role is not fillable: set by hand, the only place (with foodsy:make-admin) that writes admin
                $user->role = $account['role'];
                $user->save();
            }
        });
    }
}
