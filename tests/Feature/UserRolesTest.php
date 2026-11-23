<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Roles, ban columns and Google id on the single users table (dev/PIANO_AUTH_CLIENTI.md, step 1.2). */
class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    public function testUsersTableHasTheAccountColumns()
    {
        $this->assertTrue(Schema::hasColumns('users', ['role', 'google_id', 'avatar_url', 'banned_at', 'ban_reason', 'banned_by']));
    }

    public function testSeededAccountsAreRestaurateurs()
    {
        $this->assertSame(0, User::where('role', '!=', User::RESTAURATEUR)->count());
        $this->assertSame(User::RESTAURATEUR, User::where('email', 'federico_romiti@gmail.com')->value('role'));
    }

    public function testNewUserIsACustomerByDefaultAndNeedsNoVatNumberNorPassword()
    {
        $user = User::create(['name' => 'Ada', 'last_name' => 'Neri', 'email' => 'ada@example.com']);

        $this->assertSame(User::CUSTOMER, $user->fresh()->role);
        $this->assertNull($user->fresh()->vat_number);
        $this->assertNull($user->fresh()->password);
    }

    public function testRoleAndBanAreNotMassAssignable()
    {
        $user = User::create([
            'name' => 'Eve', 'last_name' => 'Rossa', 'email' => 'eve@example.com', 'password' => Hash::make('secret123'),
            'role' => User::ADMIN, 'banned_at' => now(), 'ban_reason' => 'x', 'google_id' => 'g-1',
        ])->fresh();

        $this->assertSame(User::CUSTOMER, $user->role);
        $this->assertNull($user->banned_at);
        $this->assertNull($user->ban_reason);
        $this->assertNull($user->google_id);
    }

    public function testRoleHelpersAndActiveAdminCount()
    {
        $admin = User::create(['name' => 'Al', 'last_name' => 'Min', 'email' => 'al@example.com']);
        $admin->role = User::ADMIN;
        $admin->save();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->hasRole(User::RESTAURATEUR, User::ADMIN));
        $this->assertFalse($admin->isBanned());
        $this->assertSame(1, User::activeAdminCount());

        $admin->banned_at = now();
        $admin->save();
        $this->assertTrue($admin->fresh()->isBanned());
        $this->assertSame(0, User::activeAdminCount());
    }

    public function testGoogleIdIsHiddenFromSerialization()
    {
        $user = User::create(['name' => 'Gi', 'last_name' => 'O', 'email' => 'gi@example.com']);
        $user->google_id = '1234567890';

        $this->assertArrayNotHasKey('google_id', $user->toArray());
    }

    public function testRegisteredPartnerIsARestaurateur()
    {
        $this->post('/register', [
            'name' => 'Pia', 'last_name' => 'Verdi', 'email' => 'pia@example.com', 'password' => 'secret123', 'password_confirmation' => 'secret123', 'vat_number' => '98765432109', 'role' => 'restaurateur',
        ]);

        $pia = User::where('email', 'pia@example.com')->first();
        $this->assertSame(User::RESTAURATEUR, $pia->role);
        $this->assertSame('Verdi', $pia->last_name);
    }
}
