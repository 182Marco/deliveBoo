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
        $this->seed();
    }

    private function averageOf($restaurantId)
    {
        return (float) DB::table('rates')->where('restaurant_id', $restaurantId)->avg('rate');
    }

    public function testTypesReturnsAllTypesWrappedInTypesKey()
    {
        $response = $this->getJson('/api/types');

        $response->assertOk()->assertJsonStructure(['types' => [['id', 'name']]]);
        $this->assertCount(Type::count(), $response->json('types'));
    }

    public function testTopRatedReturnsAtMostTwelveRestaurantsSortedByAverage()
    {
        $response = $this->getJson('/api/restaurantsTopRated');

        $response->assertOk()->assertJsonStructure([['id', 'name', 'slug', 'city', 'img', 'average']]);
        $rests = $response->json();
        $this->assertLessThanOrEqual(12, count($rests));
        $this->assertSame(min(12, Restaurant::count()), count($rests));

        $previous = INF;
        foreach ($rests as $rest) {
            $this->assertIsNumeric($rest['average']);
            $this->assertEqualsWithDelta($this->averageOf($rest['id']), $rest['average'], 0.0001, $rest['name']);
            $this->assertLessThanOrEqual($previous, $rest['average'], 'not sorted by average desc');
            $previous = $rest['average'];
        }
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
            'averageRate',
            'location' => ['id', 'name', 'slug', 'phone', 'city', 'address', 'img'],
            'types',
            'menu' => ['data' => [['id', 'name', 'price', 'restaurant_id']], 'current_page', 'last_page', 'per_page'],
        ]);
        $this->assertSame($rest->slug, $response->json('location.slug'));
        $this->assertEqualsWithDelta($this->averageOf($rest->id), $response->json('averageRate'), 0.0001);
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
        $this->assertEquals(collect($payload['plates'])->sort()->values()->all(), $order->plates()->pluck('plates.id')->sort()->values()->all());
        Mail::assertSent(OrderedMessage::class, function ($mail) use ($expected) {
            return $mail->hasTo('mario.rossi@example.com') && $mail->contact['price'] == $expected;
        });
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

    public function testRestaurantWithoutRatesHasAverageZero()
    {
        // used to be a division by zero (500) in both endpoints
        $rest = Restaurant::where('name', "Mc Donald's")->firstOrFail();
        DB::table('rates')->where('restaurant_id', $rest->id)->delete();

        $this->getJson("/api/restaurantsMenu/{$rest->slug}")->assertOk()->assertJsonPath('averageRate', 0);

        $top = collect($this->getJson('/api/restaurantsTopRated')->assertOk()->json());
        $this->assertSame(min(12, Restaurant::count()), $top->count());
        $listed = $top->firstWhere('id', $rest->id);
        if ($listed) {
            $this->assertEquals(0, $listed['average']);
        }
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
}
