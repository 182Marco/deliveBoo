<?php

namespace Tests\Feature;

use App\Cart;
use App\CartItem;
use App\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** Schema and models of the persistent cart (dev/PIANO_CARRELLO.md, step 3). */
class CartModelTest extends TestCase
{
    use RefreshDatabase;
    use SeededFixtures;

    private function mario(): User
    {
        return User::where('email', config('demo.accounts.customer.email'))->firstOrFail();
    }

    private function cartFor(User $user): Cart
    {
        $cart = new Cart;
        $cart->user_id = $user->id;
        $cart->restaurant_id = $this->ownedRestaurant()->id;
        $cart->save();

        return $cart;
    }

    public function testAccountCartWithItemsInInsertionOrder()
    {
        $cart = $this->cartFor($this->mario());
        $plates = $this->ownedRestaurant()->plates()->orderByDesc('id')->take(2)->get();
        $cart->items()->create(['plate_id' => $plates[0]->id, 'quantity' => 2]);
        $cart->items()->create(['plate_id' => $plates[1]->id, 'quantity' => 1]);

        $this->assertTrue($this->mario()->cart->is($cart));
        $this->assertFalse($cart->isGuest());
        $this->assertSame([$plates[0]->id, $plates[1]->id], $cart->items->pluck('plate_id')->all());
        $this->assertSame(2, $cart->items[0]->quantity);
        $this->assertSame(0, $cart->fresh()->version);
    }

    // owner, token and version are written only by CartService
    public function testNothingOnTheCartIsMassAssignable()
    {
        $this->expectException(MassAssignmentException::class);
        new Cart(['user_id' => 1, 'guest_token' => 'x', 'version' => 9]);
    }

    public function testOneCartPerAccount()
    {
        $this->cartFor($this->mario());

        $this->expectException(UniqueConstraintViolationException::class);
        $this->cartFor($this->mario());
    }

    public function testOneLinePerPlate()
    {
        $cart = $this->cartFor($this->mario());
        $cart->items()->create(['plate_id' => $this->ownedPlate()->id, 'quantity' => 1]);

        $this->expectException(UniqueConstraintViolationException::class);
        $cart->items()->create(['plate_id' => $this->ownedPlate()->id, 'quantity' => 3]);
    }

    public function testGuestCartHasNoAccount()
    {
        $cart = new Cart;
        $cart->guest_token = hash('sha256', 'token-from-the-cookie');
        $cart->save();

        $this->assertTrue($cart->isGuest());
        $this->assertSame(64, strlen($cart->fresh()->guest_token));
    }

    public function testSoftDeletedPlateStaysOnTheLine()
    {
        $cart = $this->cartFor($this->mario());
        $plate = $this->ownedPlate();
        $cart->items()->create(['plate_id' => $plate->id, 'quantity' => 1]);
        $plate->delete();

        $this->assertTrue($cart->items()->first()->plate->is($plate));
        $this->assertTrue($cart->items()->first()->plate->trashed());
    }

    public function testDeletingTheAccountDeletesItsCartAndLines()
    {
        $user = User::factory()->create();
        $cart = $this->cartFor($user);
        $cart->items()->create(['plate_id' => $this->ownedPlate()->id, 'quantity' => 1]);

        $user->delete();

        $this->assertNull(Cart::find($cart->id));
        $this->assertSame(0, CartItem::where('cart_id', $cart->id)->count());
    }

    // --- pruning (step 7) ---

    private function guestCartWithLine(string $token): Cart
    {
        $cart = new Cart;
        $cart->guest_token = hash('sha256', $token);
        $cart->save();
        $cart->items()->create(['plate_id' => $this->ownedPlate()->id, 'quantity' => 1]);

        return $cart;
    }

    public function testAbandonedCartsArePruned()
    {
        $oldGuest = $this->guestCartWithLine('old');
        $emptyAccount = $this->cartFor(User::factory()->create());
        $emptyAccount->items()->delete();
        $fullAccount = $this->cartFor($this->mario());
        $fullAccount->items()->create(['plate_id' => $this->ownedPlate()->id, 'quantity' => 1]);

        $this->travel(Cart::GUEST_DAYS + 1)->days();
        $recentGuest = $this->guestCartWithLine('recent');
        $recentEmpty = new Cart;
        $recentEmpty->guest_token = hash('sha256', 'recent-empty');
        $recentEmpty->save();

        $this->artisan('model:prune', ['--model' => [Cart::class]])->assertSuccessful();

        $this->assertNull(Cart::find($oldGuest->id));
        $this->assertSame(0, CartItem::where('cart_id', $oldGuest->id)->count());
        $this->assertNull(Cart::find($emptyAccount->id));
        // an account cart with lines is kept however old; recent carts are kept even when empty
        $this->assertNotNull(Cart::find($fullAccount->id));
        $this->assertNotNull(Cart::find($recentGuest->id));
        $this->assertNotNull(Cart::find($recentEmpty->id));
    }

    public function testPruningIsScheduledDaily()
    {
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($e) => str_contains($e->command, 'model:prune') && str_contains($e->command, 'Cart'));

        $this->assertNotNull($event);
        $this->assertSame('0 0 * * *', $event->expression);
    }
}
