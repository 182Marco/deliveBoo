<?php

namespace Tests\Feature;

use App\User;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Demo accounts of the portfolio, config/demo.php (dev/PIANO_AUTH_CLIENTI.md, step 2.2). */
class DemoAccountsTest extends TestCase
{
    use RefreshDatabase;

    private function demo(string $key): User
    {
        return User::where('email', config("demo.accounts.{$key}.email"))->firstOrFail();
    }

    public function testEveryDemoAccountExistsWithItsRole()
    {
        foreach (config('demo.accounts') as $key => $account) {
            $user = $this->demo($key);
            $this->assertSame($account['role'], $user->role, $key);
            $this->assertTrue(Hash::check($account['password'], $user->password), "{$key}: the password shown on the login page works");
            $this->assertTrue($user->isDemoAccount());
        }
        $this->assertSame('Mario', $this->demo('customer')->name);
        $this->assertSame('Rossi', $this->demo('customer')->last_name);
        $this->assertSame(1, User::where('role', User::ADMIN)->count());
    }

    public function testEveryDemoCredentialLogsInToItsHome()
    {
        foreach (config('demo.accounts') as $key => $account) {
            $this->post('/login', ['email' => $account['email'], 'password' => $account['password']])
                ->assertRedirect($this->demo($key)->homePath());
            $this->assertAuthenticatedAs($this->demo($key));
            $this->post('/logout');
        }
    }

    public function testSeederIsIdempotentAndKeepsChangedData()
    {
        $mario = $this->demo('customer');
        $mario->password = Hash::make('changed-by-hand');
        $mario->save();

        $this->seed(DemoAccountsSeeder::class);

        $this->assertSame(1, User::where('email', $mario->email)->count());
        $this->assertTrue(Hash::check('changed-by-hand', $mario->fresh()->password));
    }

    public function testLoginPageShowsTheDemoBoxOnlyWhenEnabled()
    {
        $this->get('/login')->assertOk()
            ->assertSee(__('partner.auth.demo.title'))
            ->assertSee(config('demo.accounts.admin.email'))
            ->assertSee(config('demo.accounts.customer.password'));

        config(['demo.enabled' => false]);
        $this->get('/login')->assertOk()->assertDontSee(__('partner.auth.demo.title'))->assertDontSee(config('demo.accounts.admin.password'));
    }

    public function testDemoAccountsCannotResetTheirPassword()
    {
        Notification::fake();

        $this->from('/password/reset')->post('/password/email', ['email' => config('demo.accounts.customer.email')])
            ->assertSessionHasErrors(['email' => __('passwords.demo')]);
        Notification::assertNothingSent();

        // the other accounts still can
        $other = User::create(['name' => 'Ugo', 'last_name' => 'Blu', 'email' => 'ugo@example.com']);
        $this->post('/password/email', ['email' => 'ugo@example.com'])->assertSessionHasNoErrors();
        Notification::assertSentTo($other, \Illuminate\Auth\Notifications\ResetPassword::class);
    }

    public function testAdminCannotEditNorBanDemoAccountsWhileTheDemoIsOn()
    {
        $admin = $this->demo('admin');
        foreach (['restaurateur', 'customer'] as $key) {
            $this->assertFalse($admin->can('update', $this->demo($key)), $key);
            $this->assertFalse($admin->can('ban', $this->demo($key)), $key);
        }

        config(['demo.enabled' => false]);
        $this->assertTrue($admin->can('update', $this->demo('customer')));
        $this->assertTrue($admin->can('ban', $this->demo('restaurateur')));
    }

    public function testMakeAdminPromotesAfterConfirmation()
    {
        $user = User::create(['name' => 'Nina', 'last_name' => 'Ocra', 'email' => 'nina@example.com']);

        $this->artisan('foodsy:make-admin', ['email' => 'nina@example.com'])
            ->expectsConfirmation('Promote Nina Ocra (nina@example.com, now customer) to platform admin?', 'no')
            ->assertFailed();
        $this->assertSame(User::CUSTOMER, $user->fresh()->role);

        $this->artisan('foodsy:make-admin', ['email' => 'nina@example.com'])
            ->expectsConfirmation('Promote Nina Ocra (nina@example.com, now customer) to platform admin?', 'yes')
            ->assertSuccessful();
        $this->assertSame(User::ADMIN, $user->fresh()->role);
    }

    public function testMakeAdminCreatesAnAccountAndRefusesBannedOnes()
    {
        $this->artisan('foodsy:make-admin', ['email' => 'new-admin@example.com'])
            ->expectsConfirmation('No account with new-admin@example.com: create a new admin?', 'yes')
            ->expectsQuestion('First name', 'Rita')
            ->expectsQuestion('Last name', 'Viola')
            ->expectsQuestion('Password (min. 8 characters)', 'long-enough-1')
            ->assertSuccessful();
        $created = User::where('email', 'new-admin@example.com')->firstOrFail();
        $this->assertTrue($created->isAdmin());
        $this->assertTrue(Hash::check('long-enough-1', $created->password));

        $banned = User::create(['name' => 'Bo', 'last_name' => 'Nero', 'email' => 'bo@example.com']);
        $banned->banned_at = now();
        $banned->save();
        $this->artisan('foodsy:make-admin', ['email' => 'bo@example.com'])->assertFailed();
        $this->assertSame(User::CUSTOMER, $banned->fresh()->role);
    }
}
