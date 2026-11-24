<?php

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\MyOrdersController;
use App\Order;
use App\Payments\FakeGateway;
use App\Plate;
use App\Restaurant;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Orders of logged-in customers: orders.user_id (step 7.1) and GET /api/me/orders (step 7.2), dev/PIANO_AUTH_CLIENTI.md. */
class CustomerOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function demo(string $role): User
    {
        return User::where('email', config("demo.accounts.{$role}.email"))->firstOrFail();
    }

    private function payload(array $overrides = []): array
    {
        $rest = Restaurant::where('name', "Mc Donald's")->firstOrFail();

        return [
            'restaurant_id' => $rest->id,
            'customer_name' => 'Mario',
            'customer_lastName' => 'Rossi',
            'customer_email' => 'mario.rossi@example.com',
            'customer_phone' => '0791234567',
            'customer_address' => 'Langstrasse 1',
            'plates' => Plate::where('restaurant_id', $rest->id)->orderBy('id')->take(2)->pluck('id')->all(),
            'payment_nonce' => FakeGateway::VALID_NONCE,
            ...$overrides,
        ];
    }

    public function testGuestCheckoutStaysWithoutAccount()
    {
        $this->postJson('/api/orders', $this->payload())->assertOk()->assertJsonPath('status', Order::PAID);

        $this->assertNull(Order::latest('id')->first()->user_id);
    }

    public function testLoggedInCustomerOwnsTheOrder()
    {
        $mario = $this->demo('customer');

        $this->actingAs($mario)->postJson('/api/orders', $this->payload())->assertOk()->assertJsonPath('status', Order::PAID);

        $order = Order::latest('id')->first();
        $this->assertSame($mario->id, $order->user_id);
        $this->assertTrue($order->user->is($mario));
        $this->assertTrue($mario->orders()->whereKey($order->id)->exists());
    }

    public function testFailedPaymentOfACustomerIsAlsoLinked()
    {
        $mario = $this->demo('customer');

        $this->actingAs($mario)->postJson('/api/orders', $this->payload(['payment_nonce' => FakeGateway::DECLINED_NONCE]))
            ->assertOk()->assertJsonStructure(['errors' => ['payment']]);

        $order = Order::latest('id')->first();
        $this->assertSame(Order::FAILED, $order->status);
        $this->assertSame($mario->id, $order->user_id);
    }

    public function testTheAccountNeverComesFromTheClient()
    {
        $mario = $this->demo('customer');

        // a guest naming someone else's account
        $this->postJson('/api/orders', $this->payload(['user_id' => $mario->id]))->assertOk();
        $this->assertNull(Order::latest('id')->first()->user_id);

        // a customer naming another account still gets their own
        $other = User::where('role', User::RESTAURATEUR)->firstOrFail();
        $this->actingAs($mario)->postJson('/api/orders', $this->payload(['user_id' => $other->id]))->assertOk();
        $this->assertSame($mario->id, Order::latest('id')->first()->user_id);
    }

    public function testPartnersAndAdminsOrderAsGuests()
    {
        foreach (['restaurateur', 'admin'] as $role) {
            $this->actingAs($this->demo($role))->postJson('/api/orders', $this->payload())->assertOk()->assertJsonPath('status', Order::PAID);
            $this->assertNull(Order::latest('id')->first()->user_id, $role);
        }
    }

    public function testBannedCustomerIsLoggedOutAndCannotOrder()
    {
        $user = User::factory()->create();
        $user->banned_at = now();
        $user->save();
        $before = Order::count();

        $this->actingAs($user)->postJson('/api/orders', $this->payload())->assertForbidden();

        $this->assertSame($before, Order::count());
        $this->assertGuest();
    }

    public function testDeletingTheAccountKeepsTheOrder()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/orders', $this->payload())->assertOk();
        $order = Order::latest('id')->first();
        $this->assertSame($user->id, $order->user_id);

        $user->delete();

        $this->assertNull($order->fresh()->user_id);
    }

    public function testMyOrdersNeedsAnAccount()
    {
        $this->getJson('/api/me/orders')->assertUnauthorized();
    }

    public function testMyOrdersListsOnlyTheOwnOrdersNewestFirstWithTheirLines()
    {
        $user = User::factory()->create();
        $payload = $this->payload();
        $payload['plates'] = [$payload['plates'][0], $payload['plates'][0], $payload['plates'][1]];
        $this->actingAs($user)->postJson('/api/orders', $payload)->assertOk();
        $this->actingAs($user)->postJson('/api/orders', $this->payload(['payment_nonce' => FakeGateway::DECLINED_NONCE]))->assertOk();
        // someone else's order with the same email is not theirs
        $this->actingAs(User::factory()->create())->postJson('/api/orders', $this->payload())->assertOk();
        $mine = $user->orders()->orderBy('id')->get();

        $response = $this->actingAs($user)->getJson('/api/me/orders')->assertOk();

        $response->assertJsonPath('total', 2)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('data.0.id', $mine[1]->id)
            ->assertJsonPath('data.0.status', Order::FAILED)
            ->assertJsonPath('data.1.id', $mine[0]->id)
            ->assertJsonPath('data.1.status', Order::PAID)
            ->assertJsonPath('data.1.fulfillment_status', Order::RECEIVED)
            ->assertJsonPath('data.1.restaurant.slug', Restaurant::find($payload['restaurant_id'])->slug);
        $paid = $response->json('data.1');
        $this->assertEqualsCanonicalizing(
            ['id', 'placed_at', 'delivered_at', 'price', 'status', 'fulfillment_status', 'address', 'restaurant', 'lines', 'review', 'can_review'],
            array_keys($paid),
        );
        $this->assertSame([2, 1], array_column($paid['lines'], 'quantity'));
        $this->assertEqualsWithDelta($paid['price'], array_sum(array_map(fn ($l) => $l['quantity'] * $l['unit_price'], $paid['lines'])), 0.001);
        // never the data of the payment or of other customers
        $this->assertStringNotContainsString('payment_error', $response->getContent());
        $this->assertStringNotContainsString('transaction_id', $response->getContent());
        $this->assertStringNotContainsString('customer_email', $response->getContent());
    }

    public function testMyOrdersIsPaginatedAndKeepsOrdersOfDeletedRestaurantsAndPlates()
    {
        $user = User::factory()->create();
        foreach (range(1, MyOrdersController::PER_PAGE + 1) as $i) {
            $this->actingAs($user)->postJson('/api/orders', $this->payload())->assertOk();
        }
        $first = $user->orders()->orderBy('id')->first();
        $first->plates->each->delete();
        $first->restaurant->delete();

        $this->actingAs($user)->getJson('/api/me/orders')->assertOk()
            ->assertJsonPath('last_page', 2)
            ->assertJsonCount(MyOrdersController::PER_PAGE, 'data');
        $this->actingAs($user)->getJson('/api/me/orders?page=2')->assertOk()
            ->assertJsonPath('data.0.id', $first->id)
            ->assertJsonPath('data.0.restaurant', null)
            ->assertJsonCount(2, 'data.0.lines');
    }

    public function testBannedAccountCannotReadItsOrders()
    {
        $user = User::factory()->create();
        $user->banned_at = now();
        $user->save();

        $this->actingAs($user)->getJson('/api/me/orders')->assertForbidden();
        // the session guard is logged out (auth:sanctum made "sanctum" the default guard of the request)
        $this->assertGuest('web');
    }
}
