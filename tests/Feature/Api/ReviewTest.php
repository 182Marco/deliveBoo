<?php

namespace Tests\Feature\Api;

use App\Order;
use App\Restaurant;
use App\Review;
use App\Reviews\RestaurantRating;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Reviews written from "My orders": PUT /api/me/orders/{order}/review (OrderPolicy@review) and the stored rating. */
class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->restaurant = Restaurant::where('name', "Mc Donald's")->firstOrFail();
        DB::table('reviews')->where('restaurant_id', $this->restaurant->id)->delete();
        RestaurantRating::refresh($this->restaurant->id);
    }

    private function customer(string $email = 'reviewer@example.com', string $role = User::CUSTOMER): User
    {
        $user = new User(['name' => 'Rita', 'last_name' => 'Reviewer', 'email' => $email, 'password' => 'secret-123']);
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function order(User $user, array $overrides = []): Order
    {
        $placed = now()->subDays(2);
        $order = new Order([
            'restaurant_id' => $this->restaurant->id, 'price' => 20, 'customer_name' => $user->name, 'customer_lastName' => 'R',
            'customer_email' => $user->email, 'customer_phone' => '1', 'customer_address' => 'Langstrasse 1',
        ]);
        $order->status = Order::PAID;
        $order->fulfillment_status = Order::DELIVERED;
        $order->user_id = $user->id;
        $order->delivered_at = $placed->copy()->addMinutes(40);
        $order->created_at = $placed;
        foreach ($overrides as $key => $value) {
            $order->{$key} = $value;
        }
        $order->save();

        return $order;
    }

    private function review(Order $order, array $data = ['rating' => 4, 'comment' => 'Hot food and a friendly rider.'])
    {
        return $this->putJson("/api/me/orders/{$order->id}/review", $data);
    }

    public function testTheCustomerReviewsADeliveredOrder()
    {
        $user = $this->customer();
        $order = $this->order($user);

        $this->actingAs($user)->review($order)->assertOk()
            ->assertJsonPath('review.rating', 4)
            ->assertJsonPath('review.comment', 'Hot food and a friendly rider.')
            ->assertJsonPath('review.hidden', false)
            ->assertJsonPath('can_review', true);

        $review = Review::where('order_id', $order->id)->firstOrFail();
        $this->assertSame($user->id, $review->user_id);
        $this->assertSame($this->restaurant->id, $review->restaurant_id);
        $fresh = $this->restaurant->fresh();
        $this->assertSame(1, $fresh->rating_count);
        $this->assertSame(4.0, $fresh->rating_avg);
        $this->assertNotNull($fresh->rating_score);
    }

    public function testEditingUpdatesTheSameReview()
    {
        $user = $this->customer();
        $order = $this->order($user);
        $this->actingAs($user)->review($order)->assertOk();
        $this->actingAs($user)->review($order, ['rating' => 2, 'comment' => '   '])->assertOk()
            ->assertJsonPath('review.rating', 2)->assertJsonPath('review.comment', null);

        $this->assertSame(1, Review::where('order_id', $order->id)->count());
        $this->assertSame(2.0, $this->restaurant->fresh()->rating_avg);
    }

    public function testValidation()
    {
        $user = $this->customer();
        $order = $this->order($user);

        $this->actingAs($user)->review($order, ['rating' => 6])->assertStatus(422)->assertJsonValidationErrors('rating');
        $this->actingAs($user)->review($order, ['rating' => 0])->assertStatus(422)->assertJsonValidationErrors('rating');
        $this->actingAs($user)->review($order, ['comment' => 'No stars'])->assertStatus(422)->assertJsonValidationErrors('rating');
        $this->actingAs($user)->review($order, ['rating' => 3, 'comment' => str_repeat('a', 1001)])->assertStatus(422)->assertJsonValidationErrors('comment');
        $this->assertSame(0, Review::where('order_id', $order->id)->count());
    }

    public function testGuestsGet401()
    {
        $order = $this->order($this->customer());

        $this->review($order)->assertUnauthorized();
    }

    public function testOnlyTheCustomerOfTheOrderCanReviewIt()
    {
        $order = $this->order($this->customer());
        $other = $this->customer('other@example.com');

        $this->actingAs($other)->review($order)->assertForbidden();
        $this->assertSame(0, Review::where('order_id', $order->id)->count());
    }

    public function testRestaurateursAndAdminsCannotReview()
    {
        foreach ([User::RESTAURATEUR, User::ADMIN] as $i => $role) {
            $user = $this->customer("staff{$i}@example.com", $role);
            $order = $this->order($user);
            $this->actingAs($user)->review($order)->assertForbidden();
        }
    }

    public function testABannedCustomerIsLoggedOut()
    {
        $user = $this->customer();
        $order = $this->order($user);
        $user->banned_at = now();
        $user->save();

        $this->actingAs($user)->review($order)->assertForbidden();
        $this->assertSame(0, Review::where('order_id', $order->id)->count());
    }

    public function testOnlyDeliveredOrdersWithinTheWindow()
    {
        $user = $this->customer();
        $cases = [
            ['fulfillment_status' => Order::PREPARING, 'delivered_at' => null],
            ['fulfillment_status' => Order::CANCELLED, 'delivered_at' => null],
            ['status' => Order::FAILED, 'fulfillment_status' => null, 'delivered_at' => null],
            ['created_at' => now()->subDays(40), 'delivered_at' => now()->subDays(31)],
        ];
        foreach ($cases as $overrides) {
            $this->actingAs($user)->review($this->order($user, $overrides))->assertForbidden();
        }
        // the last day of the window is still fine
        $this->actingAs($user)->review($this->order($user, ['created_at' => now()->subDays(30), 'delivered_at' => now()->subDays(29)]))->assertOk();
    }

    public function testUnknownOrderIs404()
    {
        $this->actingAs($this->customer())->putJson('/api/me/orders/999999/review', ['rating' => 4])->assertNotFound();
    }

    public function testAHiddenReviewCannotBeEditedAgain()
    {
        $user = $this->customer();
        $order = $this->order($user);
        $this->actingAs($user)->review($order, ['rating' => 1, 'comment' => 'Cold food.'])->assertOk();
        $review = Review::where('order_id', $order->id)->firstOrFail();
        $review->hidden_at = now();
        $review->save();

        $this->actingAs($user)->review($order, ['rating' => 5, 'comment' => 'Changed my mind.'])->assertForbidden();
        $this->assertSame('Cold food.', $review->fresh()->comment);

        // "My orders" shows it as removed, without the button
        $row = collect($this->actingAs($user)->getJson('/api/me/orders')->assertOk()->json('data'))->firstWhere('id', $order->id);
        $this->assertTrue($row['review']['hidden']);
        $this->assertFalse($row['can_review']);
    }

    public function testOnlyTheLatestReviewOfACustomerCountsInTheAverage()
    {
        $user = $this->customer();
        $older = $this->order($user, ['created_at' => now()->subDays(10), 'delivered_at' => now()->subDays(10)]);
        $newer = $this->order($user, ['created_at' => now()->subDays(3), 'delivered_at' => now()->subDays(3)]);
        $someoneElse = $this->customer('else@example.com');

        $this->actingAs($user)->review($newer, ['rating' => 5])->assertOk();
        $this->actingAs($user)->review($older, ['rating' => 1])->assertOk();
        $this->actingAs($someoneElse)->review($this->order($someoneElse), ['rating' => 3])->assertOk();

        // 5 (newest of the first customer) and 3: the old 1 star is kept but not counted
        $fresh = $this->restaurant->fresh();
        $this->assertSame(2, $fresh->rating_count);
        $this->assertSame(4.0, $fresh->rating_avg);
        $this->assertFalse(Review::where('order_id', $older->id)->value('is_current'));
        $this->assertTrue(Review::where('order_id', $newer->id)->value('is_current'));
    }

    public function testMyOrdersSaysWhetherTheButtonWritesOrEdits()
    {
        $user = $this->customer();
        $order = $this->order($user);
        $open = $this->order($user, ['fulfillment_status' => Order::PREPARING, 'delivered_at' => null]);
        $this->actingAs($user)->review($order)->assertOk();

        $rows = collect($this->actingAs($user)->getJson('/api/me/orders')->assertOk()->json('data'))->keyBy('id');
        $this->assertTrue($rows[$order->id]['can_review']);
        $this->assertSame(4, $rows[$order->id]['review']['rating']);
        $this->assertFalse($rows[$open->id]['can_review']);
        $this->assertNull($rows[$open->id]['review']);
    }
}
