<?php

namespace Tests\Feature;

use App\Cart;
use App\Carts\CartService;
use App\Plate;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** Login after shopping as a guest: CartService::merge via the Login event (dev/PIANO_CARRELLO.md, step 5). */
class CartMergeTest extends TestCase
{
    use RefreshDatabase;
    use SeededFixtures;

    private const TOKEN = 'guest-token-0123456789-0123456789-012345';

    private CartService $carts;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cart.server' => true]);
        $this->carts = app(CartService::class);
    }

    private function customer(): User
    {
        return User::create(['name' => 'Nora', 'last_name' => 'Bianchi', 'email' => 'nora@example.com', 'password' => Hash::make('secret123')]);
    }

    /** two orderable plates of Mc Donald's and one of Il cacciatore */
    private function plates(): array
    {
        $mine = Plate::orderable()->where('restaurant_id', $this->ownedRestaurant()->id)->orderBy('id')->take(2)->get();

        return [$mine[0], $mine[1], Plate::orderable()->where('restaurant_id', $this->otherRestaurant()->id)->orderBy('id')->firstOrFail()];
    }

    /** @param array<int, array{0: Plate, 1: int}> $lines */
    private function fill(?User $user, array $lines): Cart
    {
        $cart = $this->carts->findOrCreate($user, self::TOKEN);
        foreach ($lines as [$plate, $quantity]) {
            $cart = $this->carts->setQuantity($cart, $plate, $quantity);
        }

        return $cart;
    }

    private function login(User $user, string $password = 'secret123'): void
    {
        $this->withCookie(config('cart.cookie'), self::TOKEN)
            ->post('/login', ['email' => $user->email, 'password' => $password])
            ->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    /** [plate_id => quantity] of the account's cart */
    private function linesOf(User $user): array
    {
        return $user->fresh()->cart?->items()->pluck('quantity', 'plate_id')->all() ?? [];
    }

    private function guestCart(): ?Cart
    {
        return Cart::where('guest_token', CartService::hashToken(self::TOKEN))->first();
    }

    public function testEmptyAccountTakesTheGuestCart()
    {
        [$fries] = $this->plates();
        $user = $this->customer();
        $guest = $this->fill(null, [[$fries, 2]]);

        $this->login($user);

        $this->assertSame([$fries->id => 2], $this->linesOf($user));
        $this->assertNull($this->guestCart());
        $this->assertSame($guest->id, $user->fresh()->cart->id);
        $this->assertNull($user->fresh()->cart->notice);
    }

    public function testEmptyGuestCartKeepsTheAccountCart()
    {
        [, $burger] = $this->plates();
        $user = $this->customer();
        $this->fill($user, [[$burger, 1]]);

        $this->login($user);

        $this->assertSame([$burger->id => 1], $this->linesOf($user));
        $this->assertNull($user->fresh()->cart->notice);
    }

    public function testSameRestaurantKeepsTheLargerQuantityOfEachPlate()
    {
        [$fries, $burger] = $this->plates();
        $user = $this->customer();
        $this->fill($user, [[$fries, 1], [$burger, 3]]);
        $this->fill(null, [[$fries, 2], [$burger, 1]]);

        $this->login($user);

        $this->assertEquals([$fries->id => 2, $burger->id => 3], $this->linesOf($user));
        $this->assertNull($this->guestCart());
        $this->assertSame([['code' => 'merged', 'params' => []]], $user->fresh()->cart->notice);
    }

    public function testAnotherRestaurantIsReplacedByTheGuestCart()
    {
        [$fries, , $sushi] = $this->plates();
        $user = $this->customer();
        $this->fill($user, [[$sushi, 2]]);
        $this->fill(null, [[$fries, 1]]);

        $this->login($user);

        $this->assertSame([$fries->id => 1], $this->linesOf($user));
        $this->assertSame($this->ownedRestaurant()->id, $user->fresh()->cart->restaurant_id);
        $this->assertSame([['code' => 'replaced', 'params' => ['restaurant' => $this->otherRestaurant()->name]]], $user->fresh()->cart->notice);
        $this->assertSame(1, Cart::count());
    }

    public function testPlatesNoLongerOrderableAreDropped()
    {
        [$fries, $burger] = $this->plates();
        $user = $this->customer();
        $this->fill(null, [[$fries, 1], [$burger, 2]]);
        $burger->update(['availability' => Plate::SOLD_OUT]);

        $this->login($user);

        $this->assertSame([$fries->id => 1], $this->linesOf($user));
        $this->assertSame([['code' => 'removed', 'params' => ['plates' => $burger->name]]], $user->fresh()->cart->notice);
    }

    // remember-me logs in again on later requests: nothing changes the second time
    public function testMergingTwiceChangesNothing()
    {
        [$fries, $burger] = $this->plates();
        $user = $this->customer();
        $this->fill($user, [[$fries, 1]]);
        $this->fill(null, [[$fries, 3], [$burger, 1]]);

        $this->carts->merge($user, self::TOKEN);
        $version = $user->fresh()->cart->version;
        $this->carts->merge($user, self::TOKEN);

        $this->assertEquals([$fries->id => 3, $burger->id => 1], $this->linesOf($user));
        $this->assertSame($version, $user->fresh()->cart->version);
    }

    public function testMergedLinesStayWithinTheLimits()
    {
        config(['cart.max_portions' => 4]);
        [$fries, $burger] = $this->plates();
        $user = $this->customer();
        $this->fill($user, [[$fries, 3]]);
        $this->fill(null, [[$burger, 3]]);

        $this->carts->merge($user, self::TOKEN);

        $this->assertEquals([$fries->id => 3, $burger->id => 1], $this->linesOf($user));
    }

    // demo accounts are shared by many visitors: their saved cart never shows up
    public function testDemoAccountAlwaysGetsTheGuestCart()
    {
        [, , $sushi] = $this->plates();
        $mario = User::where('email', config('demo.accounts.customer.email'))->firstOrFail();
        $this->fill($mario, [[$sushi, 1]]);

        $this->login($mario, config('demo.accounts.customer.password'));

        $this->assertSame([], $this->linesOf($mario));
        $this->assertNull($mario->fresh()->cart->notice);
    }

    public function testGoogleLoginMergesToo()
    {
        config(['services.google.client_id' => 'test-client', 'services.google.client_secret' => 'test-secret']);
        [$fries] = $this->plates();
        $user = $this->customer();
        $user->google_id = 'google-sub-1';
        $user->save();
        Socialite::fake('google', GoogleUser::fake(['id' => 'google-sub-1', 'email' => $user->email, 'name' => 'Nora Bianchi', 'email_verified' => true]));
        $this->fill(null, [[$fries, 2]]);

        $this->withCookie(config('cart.cookie'), self::TOKEN)
            ->withSession(['google.mode' => 'login', 'google.role' => 'customer'])
            ->get('/auth/google/callback')->assertRedirect('/');

        $this->assertSame([$fries->id => 2], $this->linesOf($user));
    }

    public function testSignUpMergesToo()
    {
        [$fries] = $this->plates();
        $this->fill(null, [[$fries, 1]]);

        $this->withCookie(config('cart.cookie'), self::TOKEN)->post('/register', [
            'name' => 'Luca', 'last_name' => 'Verdi', 'email' => 'luca@example.com',
            'password' => 'password1', 'password_confirmation' => 'password1', 'role' => 'customer',
        ])->assertRedirect();

        $this->assertSame([$fries->id => 1], $this->linesOf(User::where('email', 'luca@example.com')->firstOrFail()));
    }

    public function testNothingHappensWhileTheServerCartIsOff()
    {
        [$fries] = $this->plates();
        $user = $this->customer();
        $this->fill(null, [[$fries, 1]]);
        config(['cart.server' => false]);

        $this->login($user);

        $this->assertNull($user->fresh()->cart);
        $this->assertNotNull($this->guestCart());
    }

    public function testTheNoticeIsShownOnce()
    {
        [$fries, , $sushi] = $this->plates();
        $user = $this->customer();
        $this->fill($user, [[$sushi, 1]]);
        $this->fill(null, [[$fries, 1]]);
        $this->carts->merge($user, self::TOKEN);

        $this->actingAs($user)->getJson('/api/cart')->assertOk()
            ->assertJsonPath('cart.owner', 'user')
            ->assertJsonPath('notices.0.code', 'replaced');
        $this->getJson('/api/cart')->assertJsonPath('notices', []);
    }
}
