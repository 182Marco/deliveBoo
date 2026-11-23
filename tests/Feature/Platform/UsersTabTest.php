<?php

namespace Tests\Feature\Platform;

use App\AuditLog;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** Tab "Users" of /platform: list, create, edit, ban, protections and audit (steps 5.1–5.3). */
class UsersTabTest extends TestCase
{
    use RefreshDatabase, SeededFixtures;

    private function admin(): User
    {
        return User::where('email', config('demo.accounts.admin.email'))->firstOrFail();
    }

    private function account(string $role = User::CUSTOMER, string $email = 'luisa@example.com'): User
    {
        $user = User::create(['name' => 'Luisa', 'last_name' => 'Neri', 'email' => $email, 'password' => Hash::make('secret123')]);
        $user->role = $role;
        $user->vat_number = $role === User::RESTAURATEUR ? '11122233344' : null;
        $user->save();

        return $user;
    }

    // ---------------------------------------------------------------- 5.1 list and detail

    public function testListFiltersAndSearch()
    {
        $this->actingAs($this->admin());
        $this->account();

        $all = $this->get('/platform/users')->assertOk()->viewData('users');
        $this->assertSame(User::count(), $all->total());

        $customers = $this->get('/platform/users?role=customer')->viewData('users');
        $this->assertSame(User::where('role', 'customer')->count(), $customers->total());

        // every word must match: first name + last name
        $found = $this->get('/platform/users?q=luisa+neri')->viewData('users');
        $this->assertSame(['luisa@example.com'], $found->pluck('email')->all());
        $this->assertSame(0, $this->get('/platform/users?q=%25')->viewData('users')->total());

        $this->assertSame(0, $this->get('/platform/users?status=banned')->viewData('users')->total());
        // unknown sort and filters fall back to the defaults
        $this->get('/platform/users?sort=password&role=superuser&status=x')->assertOk()->assertViewHas('sort', 'created_at');
    }

    public function testDetailShowsTheAccountAndItsProtections()
    {
        $this->actingAs($this->admin());
        $luisa = $this->account();

        $this->get("/platform/users/{$luisa->id}")->assertOk()->assertSee('luisa@example.com')->assertSee(__('platform.users.ban'));
        $this->get('/platform/users/'.$this->admin()->id)->assertOk()->assertSee(__('platform.users.protected.self'))->assertDontSee('name="reason"', false);
        $demo = User::where('email', config('demo.accounts.customer.email'))->first();
        $this->get("/platform/users/{$demo->id}")->assertOk()->assertSee(__('platform.users.protected.demo'))->assertDontSee('name="reason"', false);
        $this->get('/platform/users/999999')->assertNotFound();
    }

    // ---------------------------------------------------------------- 5.2 create and edit

    public function testAdminCreatesCustomersAndRestaurateurs()
    {
        $this->actingAs($this->admin());
        $base = ['name' => 'Gino', 'last_name' => 'Blu', 'password' => 'long-enough', 'password_confirmation' => 'long-enough'];

        $this->post('/platform/users', [...$base, 'role' => 'customer', 'email' => 'gino@example.com', 'vat_number' => '12345678901'])->assertRedirect();
        $gino = User::where('email', 'gino@example.com')->firstOrFail();
        $this->assertSame(User::CUSTOMER, $gino->role);
        $this->assertNull($gino->vat_number);
        $this->assertTrue(Hash::check('long-enough', $gino->password));

        $this->post('/platform/users', [...$base, 'role' => 'restaurateur', 'email' => 'gina@example.com'])->assertSessionHasErrors('vat_number');
        $this->post('/platform/users', [...$base, 'role' => 'restaurateur', 'email' => 'gina@example.com', 'vat_number' => '99988877766'])->assertRedirect();
        $this->assertSame(User::RESTAURATEUR, User::where('email', 'gina@example.com')->value('role'));

        $audit = AuditLog::where('action', AuditLog::USER_CREATED)->pluck('target_id')->all();
        $this->assertContains($gino->id, $audit);
        $this->assertStringNotContainsString('long-enough', AuditLog::pluck('meta')->toJson());
    }

    public function testAdminAccountsCannotBeCreatedFromTheForm()
    {
        $this->actingAs($this->admin());

        $this->post('/platform/users', ['role' => 'admin', 'name' => 'X', 'last_name' => 'Y', 'email' => 'x@example.com', 'password' => 'long-enough', 'password_confirmation' => 'long-enough'])
            ->assertSessionHasErrors('role');
        $this->assertSame(0, User::where('email', 'x@example.com')->count());
        $this->get('/platform/users/create')->assertOk()->assertDontSee('value="admin"', false);
    }

    public function testEditSavesPersonalDataOnlyAndAuditsTheChanges()
    {
        $this->actingAs($this->admin());
        $luisa = $this->account(User::RESTAURATEUR);

        $this->put("/platform/users/{$luisa->id}", [
            'name' => 'Luisa', 'last_name' => 'Verdi', 'email' => 'luisa.verdi@example.com', 'phone' => '044 123 45 67',
            'vat_number' => '11122233344', 'role' => 'admin', 'password' => 'hacked-123', 'banned_at' => now()->toDateTimeString(),
        ])->assertRedirect("/platform/users/{$luisa->id}");

        $luisa->refresh();
        $this->assertSame('Verdi', $luisa->last_name);
        $this->assertSame('luisa.verdi@example.com', $luisa->email);
        $this->assertSame(User::RESTAURATEUR, $luisa->role);
        $this->assertTrue(Hash::check('secret123', $luisa->password));
        $this->assertNull($luisa->banned_at);

        $entry = AuditLog::where('action', AuditLog::USER_UPDATED)->latest('id')->firstOrFail();
        $this->assertSame(['Neri', 'Verdi'], $entry->meta['changes']['last_name']);
        $this->assertEqualsCanonicalizing(['last_name', 'email', 'phone'], array_keys($entry->meta['changes']));
    }

    public function testAdminsSelfAndDemoAccountsCannotBeEdited()
    {
        $this->actingAs($this->admin());
        $otherAdmin = $this->account(User::ADMIN, 'other-admin@example.com');
        $demo = User::where('email', config('demo.accounts.restaurateur.email'))->first();
        $payload = ['name' => 'Changed', 'last_name' => 'Changed', 'email' => 'changed@example.com'];

        foreach ([$otherAdmin, $this->admin(), $demo] as $target) {
            $this->get("/platform/users/{$target->id}/edit")->assertForbidden();
            $this->put("/platform/users/{$target->id}", $payload)->assertForbidden();
            $this->assertNotSame('Changed', $target->fresh()->name);
        }
    }

    // ---------------------------------------------------------------- 5.3 ban and unban

    public function testBanNeedsAReasonLogsOutAndIsAudited()
    {
        $this->actingAs($this->admin());
        $luisa = $this->account();

        $this->post("/platform/users/{$luisa->id}/ban", ['reason' => ''])->assertSessionHasErrors('reason');
        $this->assertFalse($luisa->fresh()->isBanned());

        $this->post("/platform/users/{$luisa->id}/ban", ['reason' => 'Fraudulent orders'])->assertRedirect("/platform/users/{$luisa->id}");
        $luisa->refresh();
        $this->assertTrue($luisa->isBanned());
        $this->assertSame('Fraudulent orders', $luisa->ban_reason);
        $this->assertSame($this->admin()->id, $luisa->banned_by);
        $this->assertSame('Fraudulent orders', AuditLog::where('action', AuditLog::USER_BANNED)->latest('id')->value('meta')['reason']);

        // her open session ends at the next request; her login is refused
        $this->actingAs($luisa)->get('/')->assertRedirect('/login');
        $this->post('/login', ['email' => 'luisa@example.com', 'password' => 'secret123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($this->admin())->post("/platform/users/{$luisa->id}/unban")->assertRedirect();
        $this->assertFalse($luisa->fresh()->isBanned());
        $this->assertNull($luisa->fresh()->ban_reason);
        $this->assertSame(1, AuditLog::where('action', AuditLog::USER_UNBANNED)->count());
    }

    public function testNobodyBansThemselvesAnAdminOrADemoAccount()
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $otherAdmin = $this->account(User::ADMIN, 'other-admin@example.com');
        $demo = User::where('email', config('demo.accounts.customer.email'))->first();
        $admins = User::activeAdminCount();

        foreach ([$admin, $otherAdmin, $demo] as $target) {
            $this->post("/platform/users/{$target->id}/ban", ['reason' => 'Try'])->assertForbidden();
            $this->assertFalse($target->fresh()->isBanned());
        }
        $this->assertSame($admins, User::activeAdminCount());
        // unbanning someone who is not banned is refused too
        $this->post("/platform/users/{$this->account()->id}/unban")->assertForbidden();
    }

    public function testOnlyAdminsReachTheActions()
    {
        $luisa = $this->account();
        foreach ([$this->owner(), User::where('email', config('demo.accounts.customer.email'))->first()] as $user) {
            $this->actingAs($user)->post("/platform/users/{$luisa->id}/ban", ['reason' => 'x'])->assertRedirect($user->homePath());
            $this->actingAs($user)->postJson('/platform/users', [])->assertForbidden();
        }
        $this->assertFalse($luisa->fresh()->isBanned());
    }

    public function testRestaurantsOfABannedRestaurateurTakeNoOrders()
    {
        $restaurant = DB::table('restaurants')->whereNotNull('user_id')->first();
        $plate = DB::table('plates')->where('restaurant_id', $restaurant->id)->whereNull('deleted_at')->where('availability', 'available')->first();
        $order = fn () => $this->postJson('/api/orders', [
            'customer_name' => 'A', 'customer_lastName' => 'B', 'customer_email' => 'ab@example.com', 'customer_phone' => '1',
            'customer_address' => 'x', 'restaurant_id' => $restaurant->id, 'plates' => [$plate->id], 'payment_nonce' => 'fake-valid-nonce',
        ]);

        DB::table('users')->where('id', $restaurant->user_id)->update(['banned_at' => now()]);
        $order()->assertOk()->assertJsonPath('errors.restaurant_id.0', __('api.order.notAccepting'));

        DB::table('users')->where('id', $restaurant->user_id)->update(['banned_at' => null]);
        $order()->assertOk()->assertJsonMissingPath('errors.restaurant_id');
    }
}
