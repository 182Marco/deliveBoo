<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Characterization of laravel/ui authentication as configured in this app. */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser()
    {
        return User::create([
            'name' => 'Anna', 'last_name' => 'Bianchi', 'email' => 'anna@example.com',
            'password' => Hash::make('secret123'), 'vat_number' => '12345678901',
        ]);
    }

    public function testLoginPageRenders()
    {
        $this->get('/login')->assertOk()->assertViewIs('auth.login');
        $this->get('/register')->assertOk()->assertViewIs('auth.register');
    }

    public function testLoginRedirectsToAdmin()
    {
        $user = $this->makeUser();

        $this->post('/login', ['email' => 'anna@example.com', 'password' => 'secret123'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function testWrongPasswordIsRejected()
    {
        $this->makeUser();

        $this->from('/login')->post('/login', ['email' => 'anna@example.com', 'password' => 'nope'])
            ->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function testRegisterCreatesUserAndRedirectsToAdmin()
    {
        $this->post('/register', [
            'name' => 'Luca', 'last_name' => 'Verdi', 'email' => 'luca@example.com',
            'password' => 'password1', 'password_confirmation' => 'password1', 'vat_number' => '10987654321',
        ])->assertRedirect('/admin');

        $user = User::where('email', 'luca@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertEquals('10987654321', $user->vat_number);
        // current behaviour (bug noted in the plan): last_name is saved with the first name
        $this->assertSame('Luca', $user->last_name);
    }

    public function testRegisterRequiresElevenDigitVatNumber()
    {
        $this->post('/register', [
            'name' => 'Luca', 'last_name' => 'Verdi', 'email' => 'luca@example.com',
            'password' => 'password1', 'password_confirmation' => 'password1', 'vat_number' => '123',
        ])->assertSessionHasErrors('vat_number');
        $this->assertGuest();
    }

    public function testAuthenticatedUserIsRedirectedAwayFromLogin()
    {
        $this->actingAs($this->makeUser());

        $this->get('/login')->assertRedirect('/admin');
        $this->get('/register')->assertRedirect('/admin');
    }

    public function testLogout()
    {
        $this->actingAs($this->makeUser());

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
