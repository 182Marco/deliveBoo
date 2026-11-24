<?php

namespace Tests\Feature\Api;

use App\Restaurant;
use App\Review;
use App\Reviews\RestaurantRating;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Public reviews of the restaurant page: GET /api/restaurants/{slug}/reviews. */
class RestaurantReviewsTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->restaurant = Restaurant::where('name', "Mc Donald's")->firstOrFail();
        DB::table('reviews')->where('restaurant_id', $this->restaurant->id)->delete();
        $this->author = User::where('email', config('demo.accounts.customer.email'))->firstOrFail();
    }

    private function add(int $rating, ?string $comment, array $extra = []): Review
    {
        $review = new Review(['rating' => $rating, 'comment' => $comment]);
        $review->restaurant_id = $this->restaurant->id;
        foreach ($extra as $key => $value) {
            $review->{$key} = $value;
        }
        $review->save();

        return $review;
    }

    public function testSummaryAndVisibleReviewsWithText()
    {
        $this->add(5, 'Great food and quick delivery.', ['user_id' => $this->author->id, 'reply' => 'Thank you!', 'replied_at' => now()]);
        $this->add(4, null);
        $this->add(1, 'Rude text that an admin hid.', ['hidden_at' => now()]);
        RestaurantRating::refresh($this->restaurant->id);

        $response = $this->getJson("/api/restaurants/{$this->restaurant->slug}/reviews")->assertOk();

        $response->assertJsonPath('summary.average', 4.5)
            ->assertJsonPath('summary.count', 2)
            ->assertJsonPath('summary.distribution.5', 1)
            ->assertJsonPath('summary.distribution.4', 1)
            ->assertJsonPath('summary.distribution.1', 0)
            ->assertJsonPath('reviews.per_page', 4)
            ->assertJsonCount(1, 'reviews.data')
            ->assertJsonPath('reviews.data.0.comment', 'Great food and quick delivery.')
            ->assertJsonPath('reviews.data.0.reply', 'Thank you!')
            ->assertJsonPath('reviews.data.0.author.name', 'Mario R.');
        $this->assertSame(['name', 'avatar_seed', 'avatar_overrides'], array_keys($response->json('reviews.data.0.author')));
        $this->assertStringNotContainsString($this->author->email, $response->getContent());
    }

    public function testRestaurantWithoutReviews()
    {
        RestaurantRating::refresh($this->restaurant->id);

        $this->getJson("/api/restaurants/{$this->restaurant->slug}/reviews")->assertOk()
            ->assertJsonPath('summary.average', null)
            ->assertJsonPath('summary.count', 0)
            ->assertJsonCount(0, 'reviews.data');
    }

    public function testUnknownRestaurantIs404()
    {
        $this->getJson('/api/restaurants/does-not-exist/reviews')->assertNotFound();
    }
}
