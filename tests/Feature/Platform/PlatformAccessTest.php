<?php

namespace Tests\Feature\Platform;

use App\Analytics\Period;
use App\AuditLog;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** The /platform backoffice is for active admins only, enforced on the server (step 3.1). */
class PlatformAccessTest extends TestCase
{
    use RefreshDatabase, SeededFixtures;

    private const PAGES = ['/platform/maps', '/platform/charts', '/platform/tables', '/platform/tables/orders', '/platform/users'];

    private function admin(): User
    {
        return User::where('email', config('demo.accounts.admin.email'))->firstOrFail();
    }

    private function customer(): User
    {
        return User::where('email', config('demo.accounts.customer.email'))->firstOrFail();
    }

    public function testAdminOpensEveryTab()
    {
        $this->actingAs($this->admin());

        $this->get('/platform')->assertRedirect('/platform/maps');
        foreach (self::PAGES as $page) {
            $this->get($page)->assertOk()->assertSee(__('platform.tabs.maps'))->assertSee(__('platform.tabs.users'));
        }
        $this->get('/platform/tables/unknown')->assertNotFound();
    }

    public function testGuestsAreSentToLogin()
    {
        foreach (self::PAGES as $page) {
            $this->get($page)->assertRedirect('/login');
        }
    }

    public function testCustomersAndRestaurateursNeverSeeTheBackoffice()
    {
        foreach ([[$this->customer(), '/'], [$this->owner(), '/admin']] as [$user, $home]) {
            foreach (self::PAGES as $page) {
                $this->actingAs($user)->get($page)->assertRedirect($home);
                $this->actingAs($user)->getJson($page)->assertForbidden();
            }
        }
    }

    public function testBannedAdminIsLoggedOut()
    {
        $admin = $this->admin();
        $admin->banned_at = now();
        $admin->save();

        $this->actingAs($admin)->get('/platform/maps')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function testAdminLoginIsAudited()
    {
        $this->post('/login', ['email' => config('demo.accounts.admin.email'), 'password' => config('demo.accounts.admin.password')])
            ->assertRedirect('/platform');

        $entry = AuditLog::latest('id')->firstOrFail();
        $this->assertSame(AuditLog::ADMIN_LOGIN, $entry->action);
        $this->assertSame($this->admin()->id, $entry->actor_id);
        $this->assertSame('User', $entry->target_type);
    }

    public function testCustomerLoginIsNotAudited()
    {
        $this->post('/login', ['email' => config('demo.accounts.customer.email'), 'password' => config('demo.accounts.customer.password')]);

        $this->assertSame(0, AuditLog::count());
    }

    public function testAuditLogNeverStoresSecrets()
    {
        $this->actingAs($this->admin());
        $entry = AuditLog::record(AuditLog::USER_UPDATED, $this->customer(), ['name' => ['Mario', 'Marco'], 'password' => 'x', '_token' => 'y']);

        $this->assertSame(['name' => ['Mario', 'Marco']], $entry->fresh()->meta);
    }

    public function testFiltersFallBackToSafeDefaults()
    {
        $period = Period::fromRequest(Request::create('/', 'GET', ['from' => "2026-01-01' OR 1=1", 'to' => '2026-13-40', 'city' => 'Atlantis', 'type' => '1; DROP TABLE users']));

        $this->assertSame(now()->toDateString(), $period->to->toDateString());
        $this->assertSame(now()->subYear()->addDay()->toDateString(), $period->from->toDateString());
        $this->assertNull($period->city);
        $this->assertNull($period->typeId);
    }

    public function testFiltersKeepValidValues()
    {
        $city = Period::cities()[0];
        $type = array_key_first(Period::types());
        $period = Period::fromRequest(Request::create('/', 'GET', ['from' => '2026-03-31', 'to' => '2026-01-01', 'city' => $city, 'type' => (string) $type]));

        // swapped when reversed, inclusive days
        $this->assertSame('2026-01-01', $period->from->toDateString());
        $this->assertSame('2026-03-31', $period->to->toDateString());
        $this->assertSame(90, $period->days());
        $this->assertSame($city, $period->city);
        $this->assertSame($type, $period->typeId);
        // the previous period has the same length and ends the day before
        $this->assertSame('2025-10-03', $period->previous()->from->toDateString());
        $this->assertSame('2025-12-31', $period->previous()->to->toDateString());
        $this->assertSame(90, $period->previous()->days());
    }
}
