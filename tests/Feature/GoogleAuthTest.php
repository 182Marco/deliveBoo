<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

/** "Continue with Google" and account linking (dev/PIANO_AUTH_CLIENTI.md, step 6.1). */
class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.client_id' => 'test-client', 'services.google.client_secret' => 'test-secret']);
    }

    private function google(array $attributes = []): void
    {
        $data = [
            'id' => 'google-sub-1', 'email' => 'nora@gmail.test', 'name' => 'Nora Bianchi', 'avatar' => 'https://lh3.example.com/nora.jpg',
            'email_verified' => true, 'given_name' => 'Nora', 'family_name' => 'Bianchi', ...$attributes,
        ];
        Socialite::fake('google', GoogleUser::fake($data));
    }

    private function account(string $email, string $role = User::CUSTOMER, ?string $googleId = null, bool $password = true): User
    {
        $user = User::create(['name' => 'Old', 'last_name' => 'Account', 'email' => $email, 'password' => $password ? Hash::make('secret123') : null]);
        $user->role = $role;
        $user->google_id = $googleId;
        $user->vat_number = $role === User::RESTAURATEUR ? '55566677788' : null;
        $user->save();

        return $user;
    }

    public function testEverythingIsOffWithoutAClient()
    {
        config(['services.google.client_id' => null]);

        $this->get('/auth/google')->assertNotFound();
        $this->get('/auth/google/callback')->assertNotFound();
        $this->get('/login')->assertDontSee(__('partner.auth.google.continue'));
    }

    public function testButtonsCarryTheRoleOfTheSignUpTab()
    {
        $this->get('/login')->assertSee(__('partner.auth.google.continue'))->assertSee(route('google.redirect'), false);
        $this->get('/register?role=restaurateur')->assertSee(e(route('google.redirect', ['role' => 'restaurateur'])), false);
    }

    public function testTheRedirectKeepsTheStateInTheSession()
    {
        $this->get('/auth/google?role=restaurateur')->assertRedirectContains('accounts.google.com')->assertSessionHas('state')->assertSessionHas('google.role', 'restaurateur');
        // admin is never an allowed sign-up role
        $this->get('/auth/google?role=admin')->assertSessionHas('google.role', User::CUSTOMER);
    }

    public function testAForgedCallbackFailsOnTheState()
    {
        // real Google provider: no matching state in the session
        $this->get('/auth/google/callback?state=forged&code=abc')->assertRedirect('/login')->assertSessionHasErrors(['google' => __('partner.auth.google.state')]);
        $this->assertGuest();
    }

    public function testANewGoogleAccountIsCreatedWithTheChosenRole()
    {
        $this->google();
        $this->withSession(['google.mode' => 'login', 'google.role' => 'customer'])->get('/auth/google/callback')->assertRedirect('/');

        $nora = User::where('email', 'nora@gmail.test')->firstOrFail();
        $this->assertAuthenticatedAs($nora);
        $this->assertSame(User::CUSTOMER, $nora->role);
        $this->assertSame('google-sub-1', $nora->google_id);
        $this->assertSame(['Nora', 'Bianchi'], [$nora->name, $nora->last_name]);
        $this->assertNull($nora->password);
        $this->assertNotNull($nora->email_verified_at);
        $this->assertSame('https://lh3.example.com/nora.jpg', $nora->avatar_url);
    }

    public function testAGoogleRestaurateurCompletesTheVatNumberFirst()
    {
        $this->google();
        $this->withSession(['google.mode' => 'login', 'google.role' => 'restaurateur'])->get('/auth/google/callback')->assertRedirect('/admin');
        $nora = User::where('email', 'nora@gmail.test')->firstOrFail();
        $this->assertSame(User::RESTAURATEUR, $nora->role);

        $this->get('/admin')->assertRedirect('/account');
        $this->get('/account')->assertOk()->assertSee(__('partner.account.completeTitle'));
        $this->post('/account/profile', ['vat_number' => '123'])->assertSessionHasErrors('vat_number');
        $this->post('/account/profile', ['vat_number' => '10293847561'])->assertRedirect(route('admin.home'));
        $this->get('/admin')->assertOk();
    }

    public function testAnUnverifiedGoogleEmailIsRefused()
    {
        $this->google(['email_verified' => false]);
        $this->withSession(['google.mode' => 'login'])->get('/auth/google/callback')->assertSessionHasErrors(['google' => __('partner.auth.google.unverified')]);

        $this->assertGuest();
        $this->assertSame(0, User::where('email', 'nora@gmail.test')->count());
    }

    public function testAnExistingEmailIsNeverLinkedAutomatically()
    {
        $owner = $this->account('nora@gmail.test', User::RESTAURATEUR);
        $this->google();

        $this->withSession(['google.mode' => 'login', 'google.role' => 'customer'])->get('/auth/google/callback')
            ->assertRedirect('/login')->assertSessionHasErrors(['google' => __('partner.auth.google.exists')]);

        $this->assertGuest();
        $this->assertNull($owner->fresh()->google_id);
        $this->assertSame(User::RESTAURATEUR, $owner->fresh()->role);
    }

    public function testALinkedAccountSignsInAndKeepsItsRole()
    {
        $owner = $this->account('owner@example.com', User::RESTAURATEUR, 'google-sub-1');
        // the Google address may differ from the account email: the key is the Google id
        $this->google(['email' => 'other@gmail.test']);

        $this->withSession(['google.mode' => 'login', 'google.role' => 'customer'])->get('/auth/google/callback')->assertRedirect('/admin');
        $this->assertAuthenticatedAs($owner);
        $this->assertSame(User::RESTAURATEUR, $owner->fresh()->role);
    }

    public function testAdminsAndBannedAccountsCannotUseGoogle()
    {
        $admin = $this->account('boss@example.com', User::ADMIN, 'google-sub-1');
        $this->google();
        $this->withSession(['google.mode' => 'login'])->get('/auth/google/callback')->assertSessionHasErrors(['google' => __('partner.auth.google.admin')]);
        $this->assertGuest();

        $admin->role = User::CUSTOMER;
        $admin->banned_at = now();
        $admin->save();
        $this->withSession(['google.mode' => 'login'])->get('/auth/google/callback')->assertSessionHasErrors(['email' => __('auth.banned')]);
        $this->assertGuest();
    }

    public function testLinkingFromTheAccountPage()
    {
        $user = $this->account('me@example.com');
        $this->actingAs($user)->get('/account/google/link')->assertRedirect()->assertSessionHas('google.mode', 'link');

        $this->google(['email' => 'me.on.google@gmail.test']);
        $this->withSession(['google.mode' => 'link'])->get('/auth/google/callback')->assertRedirect('/account')->assertSessionHas('status', __('partner.auth.google.linked'));
        $this->assertSame('google-sub-1', $user->fresh()->google_id);

        // a Google account already linked to someone else cannot be linked again
        $other = $this->account('other@example.com');
        $this->actingAs($other)->withSession(['google.mode' => 'link'])->get('/auth/google/callback')->assertSessionHasErrors(['google' => __('partner.auth.google.taken')]);
        $this->assertNull($other->fresh()->google_id);
    }

    public function testUnlinkingNeedsAPassword()
    {
        $withPassword = $this->account('a@example.com', googleId: 'g-a');
        $this->actingAs($withPassword)->from('/account')->delete('/account/google')->assertRedirect('/account');
        $this->assertNull($withPassword->fresh()->google_id);

        $googleOnly = $this->account('b@example.com', googleId: 'g-b', password: false);
        $this->actingAs($googleOnly)->from('/account')->delete('/account/google')->assertSessionHasErrors(['google' => __('partner.auth.google.needPassword')]);
        $this->assertSame('g-b', $googleOnly->fresh()->google_id);
    }

    public function testAdminsCannotLinkGoogle()
    {
        $admin = $this->account('boss@example.com', User::ADMIN);
        $this->actingAs($admin)->get('/account/google/link')->assertRedirect('/account')->assertSessionHasErrors('google');
        $this->get('/account')->assertOk()->assertSee(__('partner.account.googleAdmin'));
    }
}
