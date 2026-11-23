<?php

namespace Tests\Feature\Api;

use App\Order;
use App\Payments\FakeGateway;
use App\Plate;
use App\Restaurant;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Orders of logged-in customers: orders.user_id (dev/PIANO_AUTH_CLIENTI.md, step 7.1). */
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
}
