<?php

namespace Database\Seeders;

use App\User;
use Database\Seeders\Support\CatalogPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// the generated restaurateurs (Support\CatalogPlan). Idempotent by email: existing users,
// the historic accounts included, are never changed.
class OwnersSeeder extends Seeder
{
    public function run(): void
    {
        $owners = array_filter(CatalogPlan::owners(), fn ($o) => ! $o['existing']);
        $existing = User::whereIn('email', array_column($owners, 'email'))->pluck('email')->flip();
        $usedVat = User::pluck('vat_number')->map(fn ($v) => (string) $v)->flip();
        $password = Hash::make(config('seeding.owner_password'));
        $now = now();

        $rows = [];
        foreach ($owners as $o) {
            if (isset($existing[$o['email']])) {
                continue;
            }
            if (isset($usedVat[$o['vat_number']])) {
                throw new \RuntimeException("OwnersSeeder: VAT number {$o['vat_number']} already used.");
            }
            $rows[] = [
                ...array_intersect_key($o, array_flip(['email', 'name', 'last_name', 'company_name', 'phone', 'vat_number'])),
                'role' => User::RESTAURATEUR, 'password' => $password, 'created_at' => $now, 'updated_at' => $now,
            ];
        }
        DB::transaction(fn () => collect($rows)->chunk(200)->each(fn ($c) => DB::table('users')->insert($c->values()->all())));
    }
}
