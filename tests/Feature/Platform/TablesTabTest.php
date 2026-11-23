<?php

namespace Tests\Feature\Platform;

use App\Analytics\Period;
use App\Order;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Tab "Tables" of /platform: real data, sorting, search, filters, pagination, details (step 4.4). */
class TablesTabTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('role', User::ADMIN)->firstOrFail();
    }

    private function open(string $path, array $query = [])
    {
        return $this->actingAs($this->admin())->get($path.'?'.http_build_query([...Period::presets()['all'], ...$query]));
    }

    public function testEverySectionOpens()
    {
        foreach (['restaurants', 'orders', 'customers', 'zones', 'periods'] as $section) {
            $this->open("/platform/tables/{$section}")->assertOk()->assertSee(__("platform.tables.sections.{$section}"));
        }
    }

    public function testRestaurantsAreSortedByGrossValueAndTheSortIsWhitelisted()
    {
        $rows = $this->open('/platform/tables/restaurants')->viewData('rows');
        $values = array_column($rows->items(), 'gross_value');
        $this->assertSame($values, collect($values)->sortDesc()->values()->all());
        $this->assertSame(20, $rows->perPage());

        // anything outside the whitelist falls back to the default order
        $response = $this->open('/platform/tables/restaurants', ['sort' => 'r.id; DROP TABLE users', 'dir' => 'sideways'])->assertOk();
        $this->assertSame('gross_value', $response->viewData('sort'));
        $this->assertSame('desc', $response->viewData('dir'));

        $byName = $this->open('/platform/tables/restaurants', ['sort' => 'name', 'dir' => 'asc'])->viewData('rows');
        $names = array_column($byName->items(), 'name');
        $this->assertSame($names, collect($names)->sortBy(fn ($n) => mb_strtolower($n), SORT_STRING)->values()->all());
    }

    public function testRestaurantSearchEscapesWildcards()
    {
        $name = DB::table('restaurants')->value('name');
        $found = $this->open('/platform/tables/restaurants', ['q' => mb_substr($name, 0, 6)])->viewData('rows');
        $this->assertContains($name, array_column($found->items(), 'name'));

        $this->assertSame(0, $this->open('/platform/tables/restaurants', ['q' => '%'])->viewData('rows')->total());
    }

    public function testOrdersFilterByStateSearchAndPaginate()
    {
        $failed = $this->open('/platform/tables/orders', ['state' => 'failed'])->viewData('rows');
        $this->assertGreaterThan(0, $failed->total());
        foreach ($failed->items() as $o) {
            $this->assertSame(Order::FAILED, $o->status);
        }

        $id = DB::table('orders')->where('status', Order::PAID)->value('id');
        $byId = $this->open('/platform/tables/orders', ['q' => (string) $id])->viewData('rows');
        // the exact id comes first, before the transaction ids that contain the same digits
        $this->assertSame($id, (int) $byId->items()[0]->id);

        $page2 = $this->open('/platform/tables/orders', ['page' => 2])->assertOk()->viewData('rows');
        $this->assertSame(2, $page2->currentPage());
        $this->assertStringContainsString('from=', $page2->url(3));
    }

    public function testCustomersShowWhetherTheyHaveAnAccount()
    {
        $email = DB::table('orders')->where('status', Order::PAID)->value('customer_email');
        $user = User::create(['name' => 'Reg', 'last_name' => 'Istered', 'email' => $email]);

        $rows = $this->open('/platform/tables/customers', ['q' => $email])->viewData('rows');
        $this->assertSame(1, $rows->total());
        $this->assertSame($user->id, $rows->items()[0]['user_id']);
    }

    public function testZonesAndPeriods()
    {
        $zones = $this->open('/platform/tables/zones', ['by' => 'postal_code'])->viewData('rows');
        $this->assertSame(DB::table('restaurants')->distinct()->count('postal_code'), count($zones));

        $weeks = $this->open('/platform/tables/periods', ['unit' => 'week'])->assertOk();
        $this->assertSame('week', $weeks->viewData('unit'));
        // newest first
        $buckets = array_column($weeks->viewData('rows'), 'bucket');
        $this->assertSame($buckets, collect($buckets)->sortDesc()->values()->all());
    }

    public function testDetailPages()
    {
        $order = DB::table('orders')->where('status', Order::PAID)->whereNotNull('transaction_id')->first();
        $this->open("/platform/tables/orders/{$order->id}")->assertOk()->assertSee($order->transaction_id)->assertSee($order->customer_email);

        $pending = DB::table('orders')->insertGetId(['restaurant_id' => $order->restaurant_id, 'price' => 1, 'status' => Order::PENDING, 'customer_name' => 'P', 'customer_lastName' => 'P', 'customer_email' => 'p@p.test', 'customer_phone' => '1', 'customer_address' => 'x', 'created_at' => now(), 'updated_at' => now()]);
        $this->open("/platform/tables/orders/{$pending}")->assertNotFound();
        $this->open('/platform/tables/orders/999999')->assertNotFound();

        $this->open("/platform/tables/restaurants/{$order->restaurant_id}")->assertOk();
        $this->open('/platform/tables/restaurants/999999')->assertNotFound();

        $this->open('/platform/tables/customer', ['email' => $order->customer_email])->assertOk()->assertSee($order->customer_email);
        $this->open('/platform/tables/customer', ['email' => 'not-an-email'])->assertNotFound();
        $this->open('/platform/tables/customer', ['email' => 'nobody@nowhere.test'])->assertNotFound();
    }

    public function testOnlyAdminsSeeTablesAndDetails()
    {
        $owner = User::where('role', User::RESTAURATEUR)->firstOrFail();
        $order = DB::table('orders')->value('id');
        foreach (['/platform/tables/orders', "/platform/tables/orders/{$order}", '/platform/tables/customer?email=a@b.test'] as $path) {
            $this->actingAs($owner)->get($path)->assertRedirect('/admin');
        }
    }
}
