<?php

namespace Tests\Feature\Admin;

use App\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** Reviews in the partner hub: list and public reply, only on the owner's restaurants (RestaurantPolicy@manage). */
class ReviewReplyTest extends TestCase
{
    use RefreshDatabase, SeededFixtures;

    private function review(array $extra = []): Review
    {
        $review = new Review(['rating' => 2, 'comment' => 'The food arrived cold.']);
        $review->restaurant_id = $this->ownedRestaurant()->id;
        foreach ($extra as $key => $value) {
            $review->{$key} = $value;
        }
        $review->save();

        return $review;
    }

    public function testOwnerSeesTheReviewsAndFilters()
    {
        $review = $this->review();
        $this->review(['rating' => 5, 'comment' => 'Lovely dishes.', 'reply' => 'Thanks!', 'replied_at' => now()]);
        $this->actingAs($this->owner());

        $page = $this->get(route('admin.reviews.index', $this->ownedRestaurant()->slug))->assertOk()->assertViewIs('admin.reviews.index');
        $page->assertSee('The food arrived cold.');

        $twoStars = $this->get(route('admin.reviews.index', [$this->ownedRestaurant()->slug, 'stars' => 2]))->viewData('reviews');
        $this->assertTrue($twoStars->every(fn ($r) => $r->rating === 2));
        $unanswered = $this->get(route('admin.reviews.index', [$this->ownedRestaurant()->slug, 'unanswered' => 1]))->viewData('reviews');
        $this->assertTrue($unanswered->contains('id', $review->id));
        $this->assertTrue($unanswered->every(fn ($r) => $r->reply === null));
    }

    public function testOwnerRepliesEditsAndDeletesTheReply()
    {
        $review = $this->review();
        $this->actingAs($this->owner());

        $this->put(route('admin.reviews.reply', $review->id), ['reply' => '  We are sorry, next time it will be hot.  '])->assertRedirect();
        $this->assertSame('We are sorry, next time it will be hot.', $review->fresh()->reply);
        $this->assertNotNull($review->fresh()->replied_at);

        $this->put(route('admin.reviews.reply', $review->id), ['reply' => ''])->assertSessionHasErrors('reply');
        $this->put(route('admin.reviews.reply', $review->id), ['reply' => str_repeat('a', 1001)])->assertSessionHasErrors('reply');

        $this->delete(route('admin.reviews.reply.delete', $review->id))->assertRedirect();
        $this->assertNull($review->fresh()->reply);
        $this->assertNull($review->fresh()->replied_at);
    }

    public function testAnotherPartnerCannotSeeOrReply()
    {
        $review = $this->review();
        $this->actingAs($this->otherUser());

        $this->get(route('admin.reviews.index', $this->ownedRestaurant()->slug))->assertOk()->assertViewIs('admin.notYours.restaurant');
        $this->put(route('admin.reviews.reply', $review->id), ['reply' => 'Hijacked'])->assertOk()->assertViewIs('admin.notYours.restaurant');
        $this->delete(route('admin.reviews.reply.delete', $review->id))->assertOk()->assertViewIs('admin.notYours.restaurant');
        $this->assertNull($review->fresh()->reply);
    }

    public function testAHiddenReviewCannotBeAnswered()
    {
        $review = $this->review(['hidden_at' => now()]);
        $this->actingAs($this->owner());

        $this->put(route('admin.reviews.reply', $review->id), ['reply' => 'Hello'])->assertForbidden();
        $this->get(route('admin.reviews.index', $this->ownedRestaurant()->slug))->assertOk()->assertSee(__('partner.reviews.hiddenBadge'));
    }

    public function testCustomersAndGuestsNeverReachThePartnerReviews()
    {
        $slug = $this->ownedRestaurant()->slug;
        $this->get(route('admin.reviews.index', $slug))->assertRedirect('/login');
        $this->actingAs(\App\User::where('email', config('demo.accounts.customer.email'))->firstOrFail())
            ->get(route('admin.reviews.index', $slug))->assertRedirect();
    }

    public function testUnknownRestaurantOrReviewIs404()
    {
        $this->actingAs($this->owner());
        $this->get(route('admin.reviews.index', 'does-not-exist'))->assertNotFound();
        $this->put(route('admin.reviews.reply', 999999), ['reply' => 'x'])->assertNotFound();
    }
}
