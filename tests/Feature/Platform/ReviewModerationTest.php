<?php

namespace Tests\Feature\Platform;

use App\AuditLog;
use App\Order;
use App\Restaurant;
use App\Review;
use App\Reviews\RestaurantRating;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Moderation of the reviews in /platform (Tables > Reviews): hide / show again, audit log, new average. */
class ReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->restaurant = Restaurant::where('name', "Mc Donald's")->firstOrFail();
        DB::table('reviews')->where('restaurant_id', $this->restaurant->id)->delete();
    }

    private function admin(): User
    {
        return User::where('email', config('demo.accounts.admin.email'))->firstOrFail();
    }

    private function review(int $rating, string $comment): Review
    {
        $review = new Review(['rating' => $rating, 'comment' => $comment]);
        $review->restaurant_id = $this->restaurant->id;
        $review->save();
        RestaurantRating::refresh($this->restaurant->id);

        return $review;
    }

    public function testTheAdminFindsAndHidesAReview()
    {
        $this->review(5, 'Lovely food, quick rider.');
        $rude = $this->review(1, 'What a load of crap, idiots.');
        $this->assertSame(3.0, $this->restaurant->fresh()->rating_avg);
        $this->actingAs($this->admin());

        $rows = $this->get('/platform/tables/reviews?q=' . urlencode('crap, idiots'))->assertOk()->viewData('rows');
        $this->assertSame([$rude->id], collect($rows->items())->pluck('id')->all());

        $this->from('/platform/tables/reviews')->post("/platform/reviews/{$rude->id}/hide")->assertRedirect('/platform/tables/reviews');
        $this->assertNotNull($rude->fresh()->hidden_at);
        $this->assertSame($this->admin()->id, $rude->fresh()->hidden_by);
        $fresh = $this->restaurant->fresh();
        $this->assertSame(1, $fresh->rating_count);
        $this->assertSame(5.0, $fresh->rating_avg);
        $entry = AuditLog::latest('id')->first();
        $this->assertSame(AuditLog::REVIEW_HIDDEN, $entry->action);
        $this->assertSame('Review', $entry->target_type);
        $this->assertSame($rude->id, $entry->target_id);
        $this->assertSame($this->restaurant->name, $entry->meta['restaurant']);

        // hidden: no longer public
        $public = $this->getJson("/api/restaurants/{$this->restaurant->slug}/reviews")->json('reviews.data');
        $this->assertNotContains($rude->id, array_column($public, 'id'));

        // hiding twice changes nothing and logs nothing
        $logs = AuditLog::count();
        $this->post("/platform/reviews/{$rude->id}/hide")->assertRedirect();
        $this->assertSame($logs, AuditLog::count());

        $this->post("/platform/reviews/{$rude->id}/unhide")->assertRedirect();
        $this->assertNull($rude->fresh()->hidden_at);
        $this->assertSame(3.0, $this->restaurant->fresh()->rating_avg);
        $this->assertSame(AuditLog::REVIEW_UNHIDDEN, AuditLog::latest('id')->value('action'));
    }

    public function testFiltersOfTheReviewsTable()
    {
        $this->review(5, 'Lovely food.');
        $hidden = $this->review(1, 'Rude text.');
        $hidden->hidden_at = now();
        $hidden->save();
        $this->actingAs($this->admin());

        $q = '/platform/tables/reviews?q='.urlencode($this->restaurant->name);
        $this->assertSame([$hidden->id], collect($this->get("{$q}&visibility=hidden")->viewData('rows')->items())->pluck('id')->all());
        $this->assertTrue(collect($this->get("{$q}&stars=5")->viewData('rows')->items())->every(fn ($r) => (int) $r->rating === 5));
        $this->get('/platform/tables/reviews?visibility=nonsense&stars=9&sort=hack&dir=sideways')->assertOk();
    }

    public function testOnlyTheAdminModerates()
    {
        $review = $this->review(1, 'Rude text.');
        $owner = User::find($this->restaurant->user_id);
        $customer = User::where('email', config('demo.accounts.customer.email'))->firstOrFail();

        $this->post("/platform/reviews/{$review->id}/hide")->assertRedirect('/login');
        // other roles are sent back to their own area (role middleware), JSON callers get 403
        foreach ([[$owner, '/admin'], [$customer, '/']] as [$user, $home]) {
            $this->actingAs($user)->post("/platform/reviews/{$review->id}/hide")->assertRedirect($home);
            $this->actingAs($user)->postJson("/platform/reviews/{$review->id}/hide")->assertForbidden();
        }
        $this->assertNull($review->fresh()->hidden_at);
        $this->actingAs($this->admin())->post('/platform/reviews/999999/hide')->assertNotFound();
    }

    public function testTheCustomerCannotRewriteAHiddenReview()
    {
        $customer = User::where('email', config('demo.accounts.customer.email'))->firstOrFail();
        $order = Order::where('user_id', $customer->id)->latest('id')->firstOrFail();
        $order->restaurant_id = $this->restaurant->id;
        $order->status = Order::PAID;
        $order->fulfillment_status = Order::DELIVERED;
        $order->delivered_at = now()->subDay();
        $order->save();
        Review::where('order_id', $order->id)->delete();

        $this->actingAs($customer)->putJson("/api/me/orders/{$order->id}/review", ['rating' => 1, 'comment' => 'Garbage, idiots.'])->assertOk();
        $review = Review::where('order_id', $order->id)->firstOrFail();
        $this->actingAs($this->admin())->post("/platform/reviews/{$review->id}/hide")->assertRedirect();

        $this->actingAs($customer)->putJson("/api/me/orders/{$order->id}/review", ['rating' => 5, 'comment' => 'All good now.'])->assertForbidden();
        $this->assertSame('Garbage, idiots.', $review->fresh()->comment);
        $this->assertNotNull($review->fresh()->hidden_at);
    }

    public function testTheGlobalAuditListShowsReviewEntriesWithoutAWrongUser()
    {
        $review = $this->review(1, 'Rude text.');
        $this->actingAs($this->admin())->post("/platform/reviews/{$review->id}/hide");

        $this->get('/platform/users')->assertOk()->assertSee(__('platform.users.audit.action.review.hidden'))->assertSee($this->restaurant->name);
    }
}
