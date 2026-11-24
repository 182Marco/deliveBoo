<?php

namespace Tests\Feature\Api;

use App\Mail\OrderedMessage;
use App\Order;
use App\Payments\FakeGateway;
use App\Payments\PaymentGateway;
use App\Payments\PaymentResult;
use App\Plate;
use App\Restaurant;
use App\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Characterization of the public JSON API used by the Vue SPA.
 * The shapes asserted here are what resources/js reads: do not change them.
 */
class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    // arithmetic mean of the counted reviews (visible and latest of their author), from the rows
    private function averageOf($restaurantId)
    {
        return (float) DB::table('reviews')->where('restaurant_id', $restaurantId)->where('is_current', true)->whereNull('hidden_at')->avg('rating');
    }

    public function testTypesReturnsAllTypesWrappedInTypesKey()
    {
        $response = $this->getJson('/api/types');

        $response->assertOk()->assertJsonStructure(['types' => [['id', 'name']]]);
        $this->assertCount(Type::count(), $response->json('types'));
    }

    public function testTopRatedReturnsAtMostTwelveRatedRestaurantsSortedByBayesianScore()
    {
        $response = $this->getJson('/api/restaurantsTopRated');

        $response->assertOk()->assertJsonStructure([['id', 'name', 'slug', 'city', 'img', 'average', 'count']]);
        $rests = $response->json();
        $this->assertSame(min(12, Restaurant::where('rating_count', '>', 0)->count()), count($rests));
        $this->assertGreaterThan(0, count($rests));

        $previous = INF;
        foreach ($rests as $rest) {
            // the average shown is the arithmetic mean, the order follows the stored Bayesian score
            $this->assertEqualsWithDelta($this->averageOf($rest['id']), $rest['average'], 0.006, $rest['name']);
            $this->assertGreaterThan(0, $rest['count']);
            $score = (float) Restaurant::whereKey($rest['id'])->value('rating_score');
            $this->assertLessThanOrEqual($previous, $score, 'not sorted by score desc');
            $previous = $score;
        }
    }

    public function testAFewPerfectReviewsDoNotBeatManyGoodOnes()
    {
        [$few, $many] = Restaurant::take(2)->get();
        DB::table('reviews')->whereIn('restaurant_id', [$few->id, $many->id])->delete();
        DB::table('reviews')->insert([['restaurant_id' => $few->id, 'rating' => 5], ['restaurant_id' => $few->id, 'rating' => 5]]);
        DB::table('reviews')->insert(array_fill(0, 200, ['restaurant_id' => $many->id, 'rating' => 5]));
        DB::table('reviews')->insert(array_fill(0, 40, ['restaurant_id' => $many->id, 'rating' => 4]));
        \App\Reviews\RestaurantRating::refresh($few->id);
        \App\Reviews\RestaurantRating::refresh($many->id);

        $ids = collect($this->getJson('/api/restaurantsTopRated')->assertOk()->json())->pluck('id')->all();
        $this->assertLessThan(array_search($few->id, $ids, true), array_search($many->id, $ids, true));
        // shown: the real averages (5.0 for the few, 4.83 for the many)
        $this->assertSame(5.0, Restaurant::find($few->id)->rating_avg);
        $this->assertSame(4.83, Restaurant::find($many->id)->rating_avg);
    }

    public function testRestaurantsByTypesIsPaginatedByFour()
    {
        $typeIds = Type::whereIn('name', ['Fast Food', 'Pizzeria'])->pluck('id')->all();
        $expected = DB::table('restaurant_type')->whereIn('type_id', $typeIds)->distinct()->count('restaurant_id');

        $response = $this->getJson('/api/restaurants/' . implode(',', $typeIds) . '?page=1');

        $response->assertOk()->assertJsonStructure([
            'data' => [['id', 'name', 'slug', 'img']],
            'current_page', 'last_page', 'per_page', 'total',
        ]);
        $this->assertEquals(4, $response->json('per_page'));
        $this->assertEquals(1, $response->json('current_page'));
        $this->assertEquals($expected, $response->json('total'));
        $this->assertEquals((int) ceil($expected / 4), $response->json('last_page'));

        $page2 = $this->getJson('/api/restaurants/' . implode(',', $typeIds) . '?page=2');
        $this->assertEquals(2, $page2->json('current_page'));
        $this->assertNotEquals($response->json('data.0.id'), $page2->json('data.0.id'));
    }

    public function testRestaurantMenuReturnsDetailsTypesAverageAndPaginatedMenu()
    {
        $rest = Restaurant::where('name', "Mc Donald's")->firstOrFail();

        $response = $this->getJson("/api/restaurantsMenu/{$rest->slug}?page=1");

        $response->assertOk()->assertJsonStructure([
            'rating' => ['average', 'count'],
            'location' => ['id', 'name', 'slug', 'phone', 'city', 'address', 'img'],
            'types',
            'menu' => ['data' => [['id', 'name', 'price', 'restaurant_id']], 'current_page', 'last_page', 'per_page'],
        ]);
        $this->assertSame($rest->slug, $response->json('location.slug'));
        $this->assertEqualsWithDelta($this->averageOf($rest->id), (float) $response->json('rating.average'), 0.006);
        $this->assertSame($rest->rating_count, $response->json('rating.count'));
        $this->assertEquals($rest->types()->pluck('name')->sort()->values()->all(), collect($response->json('types'))->sort()->values()->all());
        $this->assertEquals(4, $response->json('menu.per_page'));
        $this->assertEquals((int) ceil(Plate::where('restaurant_id', $rest->id)->count() / 4), $response->json('menu.last_page'));
        foreach ($response->json('menu.data') as $plate) {
            $this->assertEquals($rest->id, $plate['restaurant_id']);
        }
    }

    private function orderPayload(array $overrides = [])
    {
        $rest = Restaurant::where('name', "Mc Donald's")->firstOrFail();
        $plates = Plate::where('restaurant_id', $rest->id)->orderBy('id')->take(2)->get();

        // same shape posted by resources/js/pages/Payment (no price: the server computes it)
        return [
            'restaurant_id' => $rest->id,
            'customer_name' => 'Mario',
            'customer_lastName' => 'Rossi',
            'customer_email' => 'mario.rossi@example.com',
            'customer_phone' => '3331234567',
            'customer_address' => 'via Roma 1',
            'plates' => $plates->pluck('id')->all(),
            'payment_nonce' => FakeGateway::VALID_NONCE,
            ...$overrides,
        ];
    }

    public function testPaymentConfigOfTheFakeDriver()
    {
        $this->getJson('/api/payments/config')->assertOk()->assertExactJson(['driver' => 'fake', 'testMode' => true, 'authorization' => null]);
    }

    // the checkout shows the test cards only where no real money can move (no network call here)
    public function testBraintreeIsInTestModeOnlyOutsideProduction()
    {
        $config = ['merchant_id' => 'm', 'public_key' => 'p', 'private_key' => 'k'];

        $this->assertTrue(\App\Payments\BraintreeGateway::fromConfig([...$config, 'environment' => 'sandbox'])->testMode());
        $this->assertFalse(\App\Payments\BraintreeGateway::fromConfig([...$config, 'environment' => 'production'])->testMode());
    }

    public function testPaidOrderIsSavedWithTheServerAmountAttachesPlatesAndSendsTheMail()
    {
        Mail::fake();
        // two portions of the first plate and one of the second, and a forged client price
        $payload = $this->orderPayload();
        $payload['plates'] = [$payload['plates'][0], $payload['plates'][0], $payload['plates'][1]];
        $payload['price'] = 0.01;
        $expected = round(Plate::findMany($payload['plates'])->keyBy('id')->pipe(fn ($p) => collect($payload['plates'])->sum(fn ($id) => $p[$id]->price)), 2);
        $before = Order::count();

        $response = $this->postJson('/api/orders', $payload);

        $response->assertOk()->assertJsonPath('status', 'paid')->assertJsonMissingPath('errors');
        $this->assertSame($before + 1, Order::count());
        $order = Order::latest('id')->first();
        $this->assertSame('mario.rossi@example.com', $order->customer_email);
        $this->assertEquals($payload['restaurant_id'], $order->restaurant_id);
        $this->assertEquals($expected, $order->price);
        $this->assertSame(Order::PAID, $order->status);
        $this->assertSame('fake', $order->payment_driver);
        $this->assertStringStartsWith('fake_', $order->transaction_id);
        $this->assertSame($response->json('transaction_id'), $order->transaction_id);
        // one line per plate: the repeated id is quantity 2, with the unit price paid
        $lines = $order->plates()->get()->keyBy('id');
        $this->assertCount(2, $lines);
        $this->assertSame(2, (int) $lines[$payload['plates'][0]]->pivot->quantity);
        $this->assertSame(1, (int) $lines[$payload['plates'][2]]->pivot->quantity);
        $this->assertEqualsWithDelta($lines[$payload['plates'][0]]->price, $lines[$payload['plates'][0]]->pivot->unit_price, 0.001);
        $this->assertSame(Order::RECEIVED, $order->fulfillment_status);
        Mail::assertSent(OrderedMessage::class, function ($mail) use ($expected) {
            return $mail->hasTo('mario.rossi@example.com') && $mail->contact['price'] == $expected;
        });
    }

    public function testPlatesThatCannotBeOrderedAreRefused()
    {
        Mail::fake();
        $payload = $this->orderPayload();
        $plate = Plate::findOrFail($payload['plates'][0]);
        $plate->update(['availability' => Plate::SOLD_OUT]);
        $before = Order::count();

        $this->postJson('/api/orders', $payload)
            ->assertOk()
            ->assertJsonPath('errors.plates.0', "Not available right now: {$plate->name}.");
        $this->assertSame($before, Order::count());

        // out of season: a plate served only in a month that is not the current one
        $plate->update(['availability' => Plate::AVAILABLE, 'available_from_month' => now()->addMonths(2)->month, 'available_to_month' => now()->addMonths(3)->month]);
        $this->postJson('/api/orders', $payload)->assertOk()->assertJsonPath('errors.plates.0', "Not available right now: {$plate->name}.");
    }

    // the restaurateur sees the proof of payment: provider and transaction id
    public function testOrderDetailShowsTheProviderAndTheTransactionId()
    {
        Mail::fake();
        $this->postJson('/api/orders', $this->orderPayload())->assertOk();
        $order = Order::latest('id')->first();

        $this->actingAs($order->restaurant->user)
            ->get(route('admin.orders.show', $order->id))
            ->assertOk()
            ->assertSee('Demo payment (offline)')
            ->assertSee($order->transaction_id);
    }

    public function testDeclinedPaymentReturnsAnErrorAndKeepsAFailedOrderOutOfTheAdmin()
    {
        Mail::fake();
        $payload = $this->orderPayload(['payment_nonce' => FakeGateway::DECLINED_NONCE]);
        $paidBefore = Order::paid()->count();

        $response = $this->postJson('/api/orders', $payload);

        $response->assertOk()->assertJsonPath('errors.payment.0', 'Payment declined: Do Not Honor');
        $order = Order::latest('id')->first();
        $this->assertSame(Order::FAILED, $order->status);
        $this->assertSame('Do Not Honor', $order->payment_error);
        $this->assertNull($order->transaction_id);
        $this->assertSame($paidBefore, Order::paid()->count());
        Mail::assertNothingSent();
    }

    public function testGatewayOutageLeavesThePendingOrderAndAnswersWithAnError()
    {
        Mail::fake();
        $this->app->instance(PaymentGateway::class, new class extends FakeGateway {
            public function charge(float $amount, string $nonce, string $orderReference): PaymentResult
            {
                throw new \RuntimeException('timeout');
            }
        });

        $response = $this->postJson('/api/orders', $this->orderPayload());

        $response->assertOk()->assertJsonStructure(['errors' => ['payment']]);
        $this->assertSame(Order::PENDING, Order::latest('id')->first()->status);
        Mail::assertNothingSent();
    }

    public function testPlatesOfAnotherRestaurantAreRejectedBeforeCharging()
    {
        Mail::fake();
        $payload = $this->orderPayload();
        $other = Plate::where('restaurant_id', '!=', $payload['restaurant_id'])->firstOrFail();
        $payload['plates'][] = $other->id;
        $before = Order::count();

        $this->postJson('/api/orders', $payload)->assertOk()->assertJsonStructure(['errors' => ['plates']]);
        $this->assertSame($before, Order::count());
    }

    public function testInvalidOrderReturns200WithErrorsAndSavesNothing()
    {
        Mail::fake();
        $before = Order::count();

        $response = $this->postJson('/api/orders', ['restaurant_id' => 1]);

        // Payment.vue reads r.data.errors on a 200 response: keep this shape
        $response->assertOk()->assertJsonStructure(['errors' => [
            'customer_name', 'customer_lastName', 'customer_email', 'customer_phone', 'customer_address',
            'plates', 'payment_nonce',
        ]]);
        $this->assertIsArray($response->json('errors.customer_name'));
        $this->assertSame($before, Order::count());
        Mail::assertNothingSent();
    }

    public function testRestaurantWithoutReviewsHasNoAverageAndIsNotRanked()
    {
        // used to be a division by zero (500) in both endpoints
        $rest = Restaurant::where('name', "Mc Donald's")->firstOrFail();
        DB::table('reviews')->where('restaurant_id', $rest->id)->delete();
        \App\Reviews\RestaurantRating::refresh($rest->id);

        $this->getJson("/api/restaurantsMenu/{$rest->slug}")->assertOk()
            ->assertJsonPath('rating.average', null)->assertJsonPath('rating.count', 0);

        $top = collect($this->getJson('/api/restaurantsTopRated')->assertOk()->json());
        $this->assertNull($top->firstWhere('id', $rest->id));
    }

    public function testUnknownRestaurantMenuIs404()
    {
        $this->getJson('/api/restaurantsMenu/does-not-exist')->assertNotFound();
    }

    public function testOrderMailRenders()
    {
        $html = (new OrderedMessage([...$this->orderPayload(), 'price' => 6.5]))->render();

        $this->assertStringContainsString('Thank you, Mario!', $html);
        $this->assertStringContainsString('via Roma 1', $html);
    }

    public function testSearchFindsRestaurantsByNameWithPrefixMatchesFirst()
    {
        $rest = Restaurant::orderBy('id')->first();
        $query = mb_substr($rest->name, 0, 4);

        $response = $this->getJson('/api/restaurantsSearch?q=' . urlencode(mb_strtoupper($query)));

        $response->assertOk()->assertJsonStructure([['id', 'name', 'slug', 'city', 'img', 'price_range', 'average', 'types']]);
        $names = collect($response->json())->pluck('name');
        $this->assertLessThanOrEqual(8, $names->count());
        $this->assertTrue($names->every(fn ($name) => str_contains(mb_strtolower($name), mb_strtolower($query))));
        // the names starting with the query come before the others
        $starts = $names->map(fn ($name) => str_starts_with(mb_strtolower($name), mb_strtolower($query)));
        $this->assertSame($starts->sortDesc()->values()->all(), $starts->values()->all());
        $this->assertTrue($starts->first());
    }

    public function testSearchIgnoresShortQueriesAndTreatsWildcardsAsText()
    {
        $this->getJson('/api/restaurantsSearch?q=a')->assertOk()->assertExactJson([]);
        $this->getJson('/api/restaurantsSearch')->assertOk()->assertExactJson([]);
        $this->getJson('/api/restaurantsSearch?q=' . urlencode('%%'))->assertOk()->assertExactJson([]);
        $this->getJson('/api/restaurantsSearch?q=' . urlencode('__'))->assertOk()->assertExactJson([]);
    }
}
