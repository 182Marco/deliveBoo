<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** Roles and bans enforced on the server (dev/PIANO_AUTH_CLIENTI.md, step 1.3). */
class AccessControlTest extends TestCase
{
    use RefreshDatabase, SeededFixtures;

    private function account(string $role, bool $banned = false): User
    {
        $user = User::create(['name' => ucfirst($role), 'last_name' => 'Test', 'email' => "{$role}".($banned ? '-banned' : '').'@example.com']);
        $user->role = $role;
        $user->banned_at = $banned ? now() : null;
        $user->save();

        return $user;
    }

    public function testRestaurateurOpensThePartnerHub()
    {
        $this->actingAs($this->owner())->get(route('admin.home'))->assertOk();
    }

    public function testCustomerAndAdminCannotOpenThePartnerHub()
    {
        $this->actingAs($this->account(User::CUSTOMER))->get(route('admin.home'))->assertRedirect('/');
        $this->actingAs($this->account(User::ADMIN))->get(route('admin.restaurants.index'))->assertRedirect('/platform');
    }

    public function testWrongRoleCannotWriteInThePartnerHub()
    {
        $customer = $this->account(User::CUSTOMER);
        $rest = $this->ownedRestaurant();

        $this->actingAs($customer)->delete(route('admin.restaurants.destroy', $rest->id))->assertRedirect('/');
        $this->assertNotNull($rest->fresh());
    }

    public function testWrongRoleGetsJson403()
    {
        $this->actingAs($this->account(User::CUSTOMER))->getJson(route('admin.home'))->assertForbidden();
    }

    public function testBannedUserIsLoggedOutOnTheNextRequest()
    {
        $owner = $this->owner();
        $owner->banned_at = now();
        $owner->save();

        $this->actingAs($owner)->get(route('admin.home'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function testBannedUserIsLoggedOutEvenOnPublicPages()
    {
        $this->actingAs($this->account(User::CUSTOMER, banned: true))->get('/')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function testBannedUserGetsJson403()
    {
        $this->actingAs($this->account(User::RESTAURATEUR, banned: true))->getJson(route('admin.home'))
            ->assertForbidden()->assertJson(['message' => __('auth.banned')]);
    }

    public function testHomePathFollowsTheRole()
    {
        $this->assertSame('/', $this->account(User::CUSTOMER)->homePath());
        $this->assertSame('/admin', $this->account(User::RESTAURATEUR)->homePath());
        $this->assertSame('/platform', $this->account(User::ADMIN)->homePath());
    }

    public function testPlatformGateIsOnlyForActiveAdmins()
    {
        $this->assertTrue($this->account(User::ADMIN)->can('platform'));
        $this->assertFalse($this->account(User::ADMIN, banned: true)->can('platform'));
        $this->assertFalse($this->account(User::RESTAURATEUR)->can('platform'));
        $this->assertFalse($this->account(User::CUSTOMER)->can('platform'));
    }

    public function testUserPolicyProtectsAdminsAndSelf()
    {
        $admin = $this->account(User::ADMIN);
        $otherAdmin = User::create(['name' => 'Other', 'last_name' => 'Admin', 'email' => 'other-admin@example.com']);
        $otherAdmin->role = User::ADMIN;
        $otherAdmin->save();
        $customer = $this->account(User::CUSTOMER);
        $banned = $this->account(User::RESTAURATEUR, banned: true);

        // on ordinary accounts
        $this->assertTrue($admin->can('update', $customer));
        $this->assertTrue($admin->can('ban', $customer));
        $this->assertFalse($admin->can('unban', $customer));
        $this->assertTrue($admin->can('unban', $banned));
        $this->assertFalse($admin->can('ban', $banned));
        // never on itself nor on another admin
        foreach (['update', 'ban', 'unban'] as $ability) {
            $this->assertFalse($admin->can($ability, $admin), "self {$ability}");
            $this->assertFalse($admin->can($ability, $otherAdmin), "other admin {$ability}");
        }
        // only admins use the panel
        $this->assertFalse($customer->can('viewAny', User::class));
        $this->assertFalse($this->owner()->can('ban', $customer));
        $this->assertTrue($admin->can('create', User::class));
        // the seeded demo admin + the two created here
        $this->assertSame(User::where('role', User::ADMIN)->whereNull('banned_at')->count(), User::activeAdminCount());
        $this->assertGreaterThanOrEqual(2, User::activeAdminCount());
    }
}
