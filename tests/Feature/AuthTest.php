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
        // a partner account: role is not fillable, it is set by hand
        $user = User::create([
            'name' => 'Anna', 'last_name' => 'Bianchi', 'email' => 'anna@example.com',
            'password' => Hash::make('secret123'), 'vat_number' => '12345678901',
        ]);
        $user->role = User::RESTAURATEUR;
        $user->save();

        return $user;
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
            'password' => 'password1', 'password_confirmation' => 'password1', 'vat_number' => '10987654321', 'role' => 'restaurateur',
        ])->assertRedirect('/admin');

        $user = User::where('email', 'luca@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertEquals('10987654321', $user->vat_number);
        // the old bug (last_name saved with the first name) is fixed
        $this->assertSame('Verdi', $user->last_name);
    }

    public function testRegisterRequiresElevenDigitVatNumber()
    {
        $this->post('/register', [
            'name' => 'Luca', 'last_name' => 'Verdi', 'email' => 'luca@example.com',
            'password' => 'password1', 'password_confirmation' => 'password1', 'vat_number' => '123', 'role' => 'restaurateur',
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
    // ---------------------------------------------------------------- roles and bans (step 1.4)

    private function makeAccount(string $email, string $role, bool $banned = false)
    {
        $user = User::create(['name' => 'Role', 'last_name' => 'Test', 'email' => $email, 'password' => Hash::make('secret123')]);
        $user->role = $role;
        $user->banned_at = $banned ? now() : null;
        $user->save();

        return $user;
    }

    public function testLoginRedirectFollowsTheRole()
    {
        foreach ([[User::CUSTOMER, '/'], [User::RESTAURATEUR, '/admin'], [User::ADMIN, '/platform']] as [$role, $home]) {
            $user = $this->makeAccount("{$role}@example.com", $role);

            $this->post('/login', ['email' => "{$role}@example.com", 'password' => 'secret123'])->assertRedirect($home);
            $this->assertAuthenticatedAs($user);
            // a logged-in user opening /login goes back to the home of its role
            $this->get('/login')->assertRedirect($home);
            $this->post('/logout');
        }
    }

    public function testBannedAccountCannotLogIn()
    {
        $this->makeAccount('banned@example.com', User::CUSTOMER, banned: true);

        $this->from('/login')->post('/login', ['email' => 'banned@example.com', 'password' => 'secret123'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => __('auth.banned')]);
        $this->assertGuest();
    }

    public function testBannedAccountWithWrongPasswordGetsTheUsualError()
    {
        $this->makeAccount('banned@example.com', User::CUSTOMER, banned: true);

        $this->from('/login')->post('/login', ['email' => 'banned@example.com', 'password' => 'wrong-one'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->assertGuest();
    }

    // ---------------------------------------------------------------- tabbed sign-up (step 2.1)

    private function signUp(array $extra = [])
    {
        return $this->post('/register', [
            'name' => 'Marta', 'last_name' => 'Gialli', 'email' => 'marta@example.com',
            'password' => 'password1', 'password_confirmation' => 'password1', ...$extra,
        ]);
    }

    public function testSignUpIsACustomerByDefault()
    {
        $this->signUp()->assertRedirect('/');

        $user = User::where('email', 'marta@example.com')->firstOrFail();
        $this->assertSame(User::CUSTOMER, $user->role);
        $this->assertNull($user->vat_number);
        $this->assertAuthenticatedAs($user);
    }

    public function testCustomerSignUpIgnoresAVatNumber()
    {
        $this->signUp(['role' => 'customer', 'vat_number' => '12345678901'])->assertRedirect('/');

        $this->assertNull(User::where('email', 'marta@example.com')->value('vat_number'));
    }

    public function testSignUpCannotChooseAdmin()
    {
        $this->signUp(['role' => 'admin'])->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertSame(0, User::where('email', 'marta@example.com')->count());
    }

    public function testPartnerSignUpNeedsAFreeVatNumber()
    {
        $this->signUp(['role' => 'restaurateur'])->assertSessionHasErrors('vat_number');
        $this->signUp(['role' => 'restaurateur', 'vat_number' => (string) User::whereNotNull('vat_number')->value('vat_number')])
            ->assertSessionHasErrors('vat_number');
        $this->assertGuest();
    }

    public function testRoleQueryPreselectsTheTab()
    {
        $this->get('/register')->assertOk()->assertViewHas('role', User::CUSTOMER)->assertDontSee('name="vat_number"', false);
        $this->get('/register?role=restaurateur')->assertOk()->assertViewHas('role', User::RESTAURATEUR)
            ->assertSee('name="vat_number"', false)->assertSee('aria-current="page"', false);
        // unknown or forbidden roles fall back to customer
        $this->get('/register?role=admin')->assertOk()->assertViewHas('role', User::CUSTOMER)->assertDontSee('value="admin"', false);
    }
}
