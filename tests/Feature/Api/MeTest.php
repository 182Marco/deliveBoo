<?php

namespace Tests\Feature\Api;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** GET /api/me for the Vue site, Sanctum SPA mode (dev/PIANO_AUTH_CLIENTI.md, step 2.3). */
class MeTest extends TestCase
{
    use RefreshDatabase;

    private function mario(): User
    {
        return User::where('email', config('demo.accounts.customer.email'))->firstOrFail();
    }

    public function testGuestGetsNoUser()
    {
        $this->getJson('/api/me')->assertOk()->assertExactJson(['user' => null]);
    }

    public function testLoggedInCustomerGetsOnlyThePublicFields()
    {
        $mario = $this->mario();
        $mario->google_id = 'google-123';
        $mario->save();

        $response = $this->actingAs($mario)->getJson('/api/me')->assertOk();

        $response->assertJsonPath('user.email', $mario->email)
            ->assertJsonPath('user.role', User::CUSTOMER)
            ->assertJsonPath('user.home', '/')
            ->assertJsonPath('user.avatar_seed', $mario->avatarSeed());
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'last_name', 'email', 'phone', 'role', 'home', 'avatar_url', 'avatar_seed', 'avatar_overrides'],
            array_keys($response->json('user')),
        );
        $this->assertStringNotContainsString('google-123', $response->getContent());
    }

    public function testPartnerAndAdminGetTheirHome()
    {
        $admin = User::where('role', User::ADMIN)->firstOrFail();
        $this->actingAs($admin)->getJson('/api/me')->assertJsonPath('user.home', '/platform');
    }

    public function testBannedAccountIsLoggedOut()
    {
        $mario = $this->mario();
        $mario->banned_at = now();
        $mario->save();

        $this->actingAs($mario)->getJson('/api/me')->assertForbidden();
        $this->assertGuest();
    }

    public function testTheSessionOfTheSiteIsUsedForSameHostRequests()
    {
        // a real login on the web form, then an API call from the same host (Referer of the SPA)
        $this->post('/login', ['email' => config('demo.accounts.customer.email'), 'password' => config('demo.accounts.customer.password')]);

        $this->withHeader('Referer', 'http://localhost/')->getJson('/api/me')
            ->assertOk()->assertJsonPath('user.email', config('demo.accounts.customer.email'));
    }

    public function testTheRequestHostIsStateful()
    {
        // Codespaces and production: the host of the SPA itself is first-party (config/sanctum.php).
        // The CSRF check of stateful POSTs is skipped by Laravel in unit tests: verified by hand (see the plan, 2.3).
        $this->assertContains(\Laravel\Sanctum\Sanctum::$currentRequestHostPlaceholder, config('sanctum.stateful'));
    }
}
