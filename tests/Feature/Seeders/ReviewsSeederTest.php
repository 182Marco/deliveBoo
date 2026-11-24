<?php

namespace Tests\Feature\Seeders;

use App\Order;
use App\User;
use Database\Seeders\DemoOwnerHistorySeeder;
use Database\Seeders\ReviewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** ReviewsSeeder on the shared test seed: deterministic, idempotent, coherent with the orders. */
class ReviewsSeederTest extends TestCase
{
    use RefreshDatabase;

    private function snapshot(): array
    {
        // the demo owner's group has its own reviews (DemoOwnerHistorySeeder)
        return DB::table('reviews as v')->join('orders as o', 'o.id', '=', 'v.order_id')->join('users as u', 'u.id', '=', 'v.user_id')
            ->where(fn ($q) => $q->whereNull('o.transaction_id')->orWhere('o.transaction_id', 'not like', DemoOwnerHistorySeeder::TX_PREFIX . '%'))
            ->orderBy('u.email')->orderBy('o.created_at')
            ->get(['u.email', 'o.created_at', 'v.rating', 'v.comment', 'v.reply'])
            ->map(fn ($r) => (array) $r)->all();
    }

    public function testRunningItAgainCreatesNothing()
    {
        $before = $this->snapshot();
        $this->assertNotEmpty($before);

        $this->seed(ReviewsSeeder::class);

        $this->assertSame($before, $this->snapshot());
    }

    public function testTheSameDataOnEveryFreshRun()
    {
        $before = $this->snapshot();
        DB::table('reviews')->delete();

        $this->seed(ReviewsSeeder::class);

        $this->assertSame($before, $this->snapshot());
    }

    public function testReviewsAreOfDeliveredOrdersOfCustomersOnly()
    {
        $this->assertSame(0, DB::table('reviews as v')->join('users as u', 'u.id', '=', 'v.user_id')->where('u.role', '!=', User::CUSTOMER)->count());
        $this->assertSame(0, DB::table('reviews as v')->join('orders as o', 'o.id', '=', 'v.order_id')
            ->where(fn ($q) => $q->where('o.status', '!=', Order::PAID)->orWhere('o.fulfillment_status', '!=', Order::DELIVERED))->count());
        $this->assertSame(0, DB::table('reviews')->whereNull('order_id')->orWhereNull('user_id')->count());
        $this->assertSame(0, DB::table('reviews')->where('created_at', '>', now())->orWhere('replied_at', '>', now())->count());
        // a reply never comes without a text to answer, nor before the review
        $this->assertSame(0, DB::table('reviews')->whereNotNull('reply')->whereNull('comment')->count());
        $this->assertSame(0, DB::table('reviews')->whereColumn('replied_at', '<', 'created_at')->count());
    }

    public function testTheDemoCustomerHasRecentOrdersToReview()
    {
        $mario = User::where('email', config('demo.accounts.customer.email'))->firstOrFail();
        $recent = Order::with('review')->where('user_id', $mario->id)->where('fulfillment_status', Order::DELIVERED)
            ->where('delivered_at', '>=', now()->subDays((int) config('reviews.window_days')))->latest()->get();

        if ($recent->count() < 2) {
            $this->markTestSkipped('the test profile has fewer than two recent orders of the demo customer');
        }
        // the newest one waits for the "Review" button, the second one shows a review with a reply
        $this->assertNull($recent[0]->review);
        $this->assertTrue($mario->can('review', $recent[0]));
        $this->assertNotNull($recent[1]->review);
        $this->assertNotNull($recent[1]->review->comment);
        $this->assertNotNull($recent[1]->review->reply);
        $this->assertSame(1, $recent->filter(fn ($o) => $o->review)->count());
    }
}
