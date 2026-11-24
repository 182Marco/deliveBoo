<?php

namespace Tests\Feature\Api;

use App\Cart;
use App\Carts\CartService;
use App\Plate;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** /api/cart for the Vue site, accounts and guests (dev/PIANO_CARRELLO.md, step 4). */
class CartTest extends TestCase
{
    use RefreshDatabase;
    use SeededFixtures;

    private const COOKIE = 'foodsy_cart';

    private const TOKEN = 'guest-token-0123456789-0123456789-012345';

    protected function setUp(): void
    {
        parent::setUp();
        config(['cart.server' => true]);
    }

    // a request of the site: same host as the page (Sanctum stateful: session and encrypted cookies)
    private function site(?string $token = self::TOKEN): self
    {
        $this->withCredentials()->withHeader('Referer', config('app.url') . '/');
        if ($token !== null) {
            $this->withCookie(self::COOKIE, $token);
        }

        return $this;
    }

    private function putItem(Plate $plate, int $quantity, array $extra = []): TestResponse
    {
        return $this->site()->putJson("/api/cart/items/{$plate->id}", ['quantity' => $quantity, ...$extra]);
    }

    /** two orderable plates of Mc Donald's */
    private function plates()
    {
        return Plate::orderable()->where('restaurant_id', $this->ownedRestaurant()->id)->orderBy('id')->take(2)->get();
    }

    private function otherPlate(): Plate
    {
        return Plate::orderable()->where('restaurant_id', $this->otherRestaurant()->id)->orderBy('id')->firstOrFail();
    }

    private function guestCart(): ?Cart
    {
        return Cart::where('guest_token', CartService::hashToken(self::TOKEN))->first();
    }

    private function mario(): User
    {
        return User::where('email', config('demo.accounts.customer.email'))->firstOrFail();
    }

    public function testSwitchedOffAnswers404()
    {
        config(['cart.server' => false]);

        $this->site()->getJson('/api/cart')->assertNotFound();
        $this->putItem($this->plates()[0], 1)->assertNotFound();
    }

    public function testFirstVisitGetsAnIdButNoCart()
    {
        $response = $this->site(null)->getJson('/api/cart')->assertOk();

        $response->assertExactJson([
            'cart' => ['owner' => 'guest', 'version' => 0, 'restaurant' => null, 'items' => [], 'count' => 0, 'total' => 0],
            'notices' => [],
        ]);
        $cookie = $response->getCookie(self::COOKIE);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertGreaterThan(now()->addDays(399)->getTimestamp(), $cookie->getExpiresTime());
        $this->assertSame(0, Cart::count());
    }

    public function testGuestCartFollowsItsCookieAndKeepsOnlyTheHash()
    {
        [$fries] = $this->plates();

        $this->putItem($fries, 2)->assertOk()
            ->assertJsonPath('cart.owner', 'guest')
            ->assertJsonPath('cart.items.0.plate_id', $fries->id)
            ->assertJsonPath('cart.items.0.quantity', 2)
            ->assertJsonPath('cart.restaurant.slug', $this->ownedRestaurant()->slug)
            ->assertJsonPath('cart.count', 2)
            ->assertJsonPath('cart.total', fn ($total) => abs($total - 2 * $fries->price) < 0.001);

        $this->assertNotNull($this->guestCart());
        $this->assertSame(0, Cart::where('guest_token', self::TOKEN)->count());

        $this->site()->getJson('/api/cart')->assertJsonPath('cart.count', 2);
        $this->site('another-token-0123456789-0123456789-0123')->getJson('/api/cart')->assertJsonPath('cart.count', 0);
    }

    public function testQuantityIsAbsoluteSoRepeatingARequestChangesNothing()
    {
        [$fries, $burger] = $this->plates();

        $this->putItem($fries, 2);
        $this->putItem($fries, 2);
        $response = $this->putItem($burger, 1)->assertOk();

        $this->assertSame([[$fries->id, 2], [$burger->id, 1]], collect($response->json('cart.items'))->map(fn ($l) => [$l['plate_id'], $l['quantity']])->all());
        $this->assertSame(3, $response->json('cart.version'));
    }

    public function testRemovingIsIdempotent()
    {
        [$fries, $burger] = $this->plates();
        $this->putItem($fries, 2);
        $this->putItem($burger, 1);

        $this->putItem($fries, 0)->assertOk()->assertJsonPath('cart.count', 1);
        $this->site()->deleteJson("/api/cart/items/{$fries->id}")->assertOk()->assertJsonPath('cart.count', 1);
        $this->site()->deleteJson('/api/cart')->assertOk()->assertJsonPath('cart.items', [])->assertJsonPath('cart.restaurant', null);
        $this->site()->deleteJson('/api/cart')->assertOk();
        $this->assertNull($this->guestCart()->restaurant_id);

        // nothing to remove for a browser without a cart: no cart is created
        $this->site('fresh-token-0123456789-0123456789-012345')->deleteJson('/api/cart')->assertOk();
        $this->assertSame(1, Cart::count());
    }

    public function testAnotherRestaurantIsRefusedUnlessReplacing()
    {
        [$fries] = $this->plates();
        $sushi = $this->otherPlate();
        $this->putItem($fries, 1);

        $this->putItem($sushi, 1)->assertStatus(409)
            ->assertJsonPath('code', 'other_restaurant')
            ->assertJsonPath('cart.items.0.plate_id', $fries->id)
            ->assertJsonPath('cart.count', 1);

        $this->putItem($sushi, 2, ['replace' => true])->assertOk()
            ->assertJsonPath('cart.restaurant.id', $this->otherRestaurant()->id)
            ->assertJsonPath('cart.items.0.plate_id', $sushi->id)
            ->assertJsonPath('cart.count', 2);
    }

    public function testAPlateThatCannotBeOrderedCanOnlyBeLowered()
    {
        [$fries] = $this->plates();
        $this->putItem($fries, 3);
        $fries->update(['availability' => Plate::SOLD_OUT]);

        $this->putItem($fries, 4)->assertStatus(422)->assertJsonPath('code', 'unavailable')->assertJsonPath('cart.items.0.quantity', 3);
        $this->putItem($fries, 1)->assertOk()
            ->assertJsonPath('cart.items.0.quantity', 1)
            ->assertJsonPath('cart.items.0.plate.orderable', false);
    }

    public function testADeletedPlateStaysVisibleAndCanBeRemoved()
    {
        [$fries] = $this->plates();
        $this->putItem($fries, 1);
        $fries->delete();

        $this->site()->getJson('/api/cart')->assertJsonPath('cart.items.0.plate.orderable', false);
        $this->putItem($fries, 2)->assertStatus(422)->assertJsonPath('code', 'unavailable');
        $this->site()->deleteJson("/api/cart/items/{$fries->id}")->assertOk()->assertJsonPath('cart.count', 0);
    }

    public function testUnknownPlateAndInvalidQuantity()
    {
        $this->site()->putJson('/api/cart/items/999999', ['quantity' => 1])->assertNotFound();
        $this->putItem($this->plates()[0], 21)->assertStatus(422)->assertJsonValidationErrors('quantity');
        $this->putItem($this->plates()[0], -1)->assertStatus(422)->assertJsonValidationErrors('quantity');
        $this->site()->putJson("/api/cart/items/{$this->plates()[0]->id}", [])->assertStatus(422);
    }

    public function testTheWholeCartHasALimit()
    {
        config(['cart.max_portions' => 5]);
        [$fries, $burger] = $this->plates();
        $this->putItem($fries, 4);

        $this->putItem($burger, 2)->assertStatus(422)->assertJsonPath('code', 'too_many')->assertJsonPath('cart.count', 4);
        $this->putItem($burger, 1)->assertOk()->assertJsonPath('cart.count', 5);
    }

    public function testPricesAlwaysComeFromTheMenu()
    {
        [$fries] = $this->plates();
        $this->putItem($fries, 2);
        $fries->update(['price' => 3.25]);

        $this->site()->getJson('/api/cart')
            ->assertJsonPath('cart.items.0.plate.price', 3.25)
            ->assertJsonPath('cart.total', 6.5);
    }

    public function testLoggedInAccountHasItsOwnCart()
    {
        [$fries, $burger] = $this->plates();
        $this->putItem($fries, 1);

        $this->actingAs($this->mario());
        $this->putItem($burger, 3)->assertOk()
            ->assertJsonPath('cart.owner', 'user')
            ->assertJsonPath('cart.count', 3);

        $this->assertSame(3, $this->mario()->cart->items()->sum('quantity'));
        $this->assertSame(1, (int) $this->guestCart()->items()->sum('quantity'));
    }

    public function testBannedAccountIsLoggedOut()
    {
        $mario = $this->mario();
        $mario->banned_at = now();
        $mario->save();

        $this->actingAs($mario);
        $this->putItem($this->plates()[0], 1)->assertForbidden();
    }

    public function testRestaurantOfABannedPartnerTakesNoDishes()
    {
        $owner = $this->owner();
        $owner->banned_at = now();
        $owner->save();

        $this->putItem($this->plates()[0], 1)->assertStatus(422)->assertJsonPath('code', 'not_accepting');
    }

    public function testImportFillsOnlyAnEmptyCart()
    {
        [$fries, $burger] = $this->plates();
        $sushi = $this->otherPlate();

        $this->site()->postJson('/api/cart/import', ['items' => [
            ['plate_id' => $fries->id, 'quantity' => 2],
            ['plate_id' => 999999, 'quantity' => 1],
            ['plate_id' => $sushi->id, 'quantity' => 1],
            ['plate_id' => $burger->id, 'quantity' => 1],
        ]])->assertOk()
            ->assertJsonPath('cart.count', 3)
            ->assertJsonPath('cart.restaurant.id', $this->ownedRestaurant()->id);

        // a second import (another tab, a reload) changes nothing
        $this->site()->postJson('/api/cart/import', ['items' => [['plate_id' => $burger->id, 'quantity' => 5]]])
            ->assertOk()->assertJsonPath('cart.count', 3);
    }

    // the cart has its own throttle counter: many clicks never block the checkout (throttle:10,1)
    public function testCartRequestsDoNotUseUpTheCheckoutLimit()
    {
        [$fries] = $this->plates();
        for ($i = 1; $i <= 15; $i++) {
            $this->putItem($fries, $i % 3 + 1)->assertOk();
        }

        $response = $this->site()->postJson('/api/orders', []);

        $this->assertNotSame(429, $response->status());
        $response->assertHeader('X-RateLimit-Remaining', 9);
    }

    // two first writes at the same time (two tabs): the unique index lets one insert win, the other reuses it
    public function testConcurrentCreationEndsInOneCart()
    {
        $hash = CartService::hashToken(self::TOKEN);
        Cart::creating(function () use ($hash) {
            Cart::flushEventListeners();
            \Illuminate\Support\Facades\DB::table('carts')->insert(['guest_token' => $hash, 'version' => 0, 'created_at' => now(), 'updated_at' => now()]);
        });

        $cart = app(CartService::class)->findOrCreate(null, self::TOKEN);

        $this->assertSame($hash, $cart->guest_token);
        $this->assertSame(1, Cart::count());
    }

    public function testRoutesAreThrottledAndOnlyNumericPlates()
    {
        // no cart route matches (the SPA catch-all only answers GET: 405)
        $this->assertTrue($this->site()->putJson('/api/cart/items/fries', ['quantity' => 1])->isClientError());
        $this->assertSame(0, Cart::count());
        $this->site()->getJson('/api/cart')->assertHeader('X-RateLimit-Limit', 120);
    }
}
