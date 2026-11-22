<?php

namespace Tests\Feature\Api;

use App\Mail\OrderedMessage;
use App\Order;
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

    private function orderPayload()
    {
        $rest = Restaurant::where('name', "Mc Donald's")->firstOrFail();
        $plates = Plate::where('restaurant_id', $rest->id)->orderBy('id')->take(2)->get();

        // same shape posted by resources/js/pages/Payment.vue
        return [
            'restaurant_id' => $rest->id,
            'price' => $plates->sum('price'),
            'customer_name' => 'Mario',
            'customer_lastName' => 'Rossi',
            'customer_email' => 'mario.rossi@example.com',
            'customer_phone' => '3331234567',
            'customer_address' => 'via Roma 1',
            'plates' => $plates->pluck('id')->all(),
        ];
    }

    public function testStoringAValidOrderSavesItAttachesPlatesAndSendsTheMail()
    {
        Mail::fake();
        $payload = $this->orderPayload();
        $before = Order::count();

        $response = $this->postJson('/api/orders', $payload);

        $response->assertOk()->assertExactJson($payload);
        $this->assertSame($before + 1, Order::count());
        $order = Order::latest('id')->first();
        $this->assertSame('mario.rossi@example.com', $order->customer_email);
        $this->assertEquals($payload['restaurant_id'], $order->restaurant_id);
        $this->assertEquals(collect($payload['plates'])->sort()->values()->all(), $order->plates()->pluck('plates.id')->sort()->values()->all());
        Mail::assertSent(OrderedMessage::class, function ($mail) {
            return $mail->hasTo('mario.rossi@example.com');
        });
    }

    public function testInvalidOrderReturns200WithErrorsAndSavesNothing()
    {
        Mail::fake();
        $before = Order::count();

        $response = $this->postJson('/api/orders', ['restaurant_id' => 1]);

        // Payment.vue reads r.data.errors on a 200 response: keep this shape
        $response->assertOk()->assertJsonStructure(['errors' => [
            'customer_name', 'customer_lastName', 'customer_email', 'customer_phone', 'customer_address',
        ]]);
        $this->assertIsArray($response->json('errors.customer_name'));
        $this->assertSame($before, Order::count());
        Mail::assertNothingSent();
    }

    public function testOrderMailRenders()
    {
        $html = (new OrderedMessage($this->orderPayload()))->render();

        $this->assertStringContainsString('Mario', $html);
    }
}
