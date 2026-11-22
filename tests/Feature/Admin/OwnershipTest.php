<?php

namespace Tests\Feature\Admin;

use App\Plate;
use App\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/**
 * Who can see / change what in the admin area.
 *
 * Tests named *_CurrentBehaviour pin down existing vulnerabilities (SEC-n, see
 * dev/PIANO_MIGRAZIONE_L13.md): they must stay green during the migration so the
 * behaviour does not change by accident. The matching testOtherUserCannot* test
 * describes the secure behaviour and is skipped until the separate
 * "fix(security): SEC-n" commit, which deletes the _CurrentBehaviour test.
 */
class OwnershipTest extends TestCase
{
    use RefreshDatabase, SeededFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** [route name, route param, view for the owner, view for another user] */
    private function pages()
    {
        $rest = $this->ownedRestaurant();
        $plate = $this->ownedPlate();
        $order = $this->ownedOrder();

        return [
            ['admin.restaurants.show', $rest->id, 'admin.restaurants.show', 'admin.notYours.restaurant'],
            ['admin.restaurants.edit', $rest->id, 'admin.restaurants.edit', 'admin.notYours.restaurant'],
            ['admin.plates.index', $rest->slug, 'admin.plates.index', 'admin.notYours.plate'],
            ['admin.plates.show', $plate->slug, 'admin.plates.show', 'admin.notYours.plate'],
            ['admin.plates.edit', $plate->slug, 'admin.plates.edit', 'admin.notYours.plate'],
            ['admin.orders.index', $rest->slug, 'admin.orders.index', 'admin.notYours.orders'],
            ['admin.orders.show', $order->id, 'admin.orders.show', 'admin.notYours.plate'],
        ];
    }

    public function testOwnerSeesEveryPageOfHisRestaurant()
    {
        $this->actingAs($this->owner());

        foreach ($this->pages() as [$name, $param, $ownerView]) {
            $this->get(route($name, $param))->assertOk()->assertViewIs($ownerView);
        }
        $this->get(route('admin.home'))->assertOk()->assertViewIs('admin.restaurants.index');
        $this->get(route('admin.restaurants.index'))->assertOk()->assertViewIs('admin.restaurants.index');
        $this->get(route('admin.restaurants.create'))->assertOk()->assertViewIs('admin.restaurants.create');
        $this->get(route('admin.plates.create', $this->ownedRestaurant()->slug))->assertOk()->assertViewIs('admin.plates.create');
        $this->get(route('admin.stats.show', $this->ownedRestaurant()->id))->assertOk()->assertViewIs('admin.stats.show');
    }

    public function testOtherUserSeesNotYoursPages()
    {
        $this->actingAs($this->otherUser());

        foreach ($this->pages() as [$name, $param, , $otherView]) {
            $this->get(route($name, $param))->assertOk()->assertViewIs($otherView);
        }
    }

    public function testRestaurantIndexListsOnlyOwnRestaurants()
    {
        $this->actingAs($this->otherUser());

        $listed = $this->get(route('admin.restaurants.index'))->viewData('auth_restaurants');
        $this->assertNotEmpty($listed->items());
        foreach ($listed->items() as $rest) {
            $this->assertEquals($this->otherUser()->id, $rest->user_id);
        }
    }

    public function testPlateCreateFormForAnotherRestaurantSlug_CurrentBehaviour()
    {
        // not a data leak (the form lists only the user's own restaurants), kept as is
        $this->actingAs($this->otherUser());

        $response = $this->get(route('admin.plates.create', $this->ownedRestaurant()->slug));

        $response->assertOk()->assertViewIs('admin.plates.create');
        foreach ($response->viewData('res_of_user') as $rest) {
            $this->assertEquals($this->otherUser()->id, $rest->user_id);
        }
    }

    public function testGuestIsRedirectedToLoginEverywhere()
    {
        foreach ($this->pages() as [$name, $param]) {
            $this->get(route($name, $param))->assertRedirect('/login');
        }
        $this->get(route('admin.home'))->assertRedirect('/login');
        $this->get(route('admin.stats.show', $this->ownedRestaurant()->id))->assertRedirect('/login');
        $this->get(route('admin.plates.create', $this->ownedRestaurant()->slug))->assertRedirect('/login');
    }

    // ---------------------------------------------------------------- PlateController@store (SEC-1)

    private function platePayload($restaurantId, $name)
    {
        return [
            'name' => $name,
            'ingredients' => 'flour, water',
            'description' => 'test plate',
            'visible' => 1,
            'price' => 7.5,
            'restaurant_id' => $restaurantId,
        ];
    }

    public function testOwnerCanStorePlateInOwnRestaurant()
    {
        $this->actingAs($this->owner());

        $response = $this->post(route('admin.plates.store'), $this->platePayload($this->ownedRestaurant()->id, 'Owner Plate'));

        $response->assertRedirect(route('admin.plates.show', 'owner-plate'));
        $this->assertEquals($this->ownedRestaurant()->id, Plate::where('slug', 'owner-plate')->value('restaurant_id'));
    }

    public function testOtherUserCanStorePlateInForeignRestaurant_CurrentBehaviour()
    {
        // SEC-1: restaurant_id comes from the form and is never checked against auth()->id()
        $this->actingAs($this->otherUser());

        $this->post(route('admin.plates.store'), $this->platePayload($this->ownedRestaurant()->id, 'Intruder Plate'))
            ->assertRedirect(route('admin.plates.show', 'intruder-plate'));

        $this->assertEquals($this->ownedRestaurant()->id, Plate::where('slug', 'intruder-plate')->value('restaurant_id'));
    }

    public function testOtherUserCannotStorePlateInForeignRestaurant()
    {
        $this->markTestSkipped('SEC-1: PlateController@store does not check the restaurant owner (separate fix(security) commit).');

        $this->actingAs($this->otherUser());
        $this->post(route('admin.plates.store'), $this->platePayload($this->ownedRestaurant()->id, 'Intruder Plate'))
            ->assertViewIs('admin.notYours.plate');
        $this->assertSame(0, Plate::where('slug', 'intruder-plate')->count());
    }

    public function testGuestCannotStorePlate()
    {
        $this->post(route('admin.plates.store'), $this->platePayload($this->ownedRestaurant()->id, 'Guest Plate'))
            ->assertRedirect('/login');

        $this->assertSame(0, Plate::where('slug', 'guest-plate')->count());
    }

    // ---------------------------------------------------------------- StatsController@show (SEC-2)

    public function testOwnerSeesStatsOfOwnRestaurant()
    {
        $this->actingAs($this->owner());
        $rest = $this->ownedRestaurant();

        $orders = $this->get(route('admin.stats.show', $rest->id))->assertOk()->assertViewIs('admin.stats.show')->viewData('related_orders');

        $this->assertNotEmpty($orders);
        foreach ($orders as $order) {
            $this->assertEquals($rest->id, $order->restaurant_id);
        }
    }

    public function testOtherUserSeesForeignStats_CurrentBehaviour()
    {
        // SEC-2: orders (with customer names, emails, addresses) of any restaurant are readable
        $this->actingAs($this->otherUser());

        $response = $this->get(route('admin.stats.show', $this->ownedRestaurant()->id));

        $response->assertOk()->assertViewIs('admin.stats.show');
        $this->assertNotEmpty($response->viewData('related_orders'));
    }

    public function testOtherUserCannotSeeForeignStats()
    {
        $this->markTestSkipped('SEC-2: StatsController@show does not check the restaurant owner (separate fix(security) commit).');

        $this->actingAs($this->otherUser());
        $this->get(route('admin.stats.show', $this->ownedRestaurant()->id))->assertViewIs('admin.notYours.orders');
    }

    public function testGuestCannotSeeStats()
    {
        $this->get(route('admin.stats.show', $this->ownedRestaurant()->id))->assertRedirect('/login');
    }

    // ---------------------------------------------------------------- update / destroy (SEC-3, SEC-4)

    public function testOtherUserCanUpdateAndDeleteForeignRestaurant_CurrentBehaviour()
    {
        // SEC-3: RestaurantController@update / @destroy have no ownership check
        $this->actingAs($this->otherUser());
        $rest = $this->ownedRestaurant();

        $this->patch(route('admin.restaurants.update', $rest->id), [
            'name' => 'Hijacked', 'phone' => '1', 'city' => 'X', 'address' => 'Y',
        ])->assertRedirect(route('admin.restaurants.show', $rest->id));
        $this->assertSame('Hijacked', $rest->fresh()->name);

        $this->delete(route('admin.restaurants.destroy', $rest->id))->assertRedirect(route('admin.restaurants.index'));
        $this->assertNull(Restaurant::find($rest->id));
    }

    public function testOtherUserCannotUpdateOrDeleteForeignRestaurant()
    {
        $this->markTestSkipped('SEC-3: RestaurantController@update/@destroy do not check the owner (separate fix(security) commit).');

        $this->actingAs($this->otherUser());
        $rest = $this->ownedRestaurant();
        $this->patch(route('admin.restaurants.update', $rest->id), ['name' => 'Hijacked', 'phone' => '1', 'city' => 'X', 'address' => 'Y'])
            ->assertViewIs('admin.notYours.restaurant');
        $this->delete(route('admin.restaurants.destroy', $rest->id))->assertViewIs('admin.notYours.restaurant');
        $this->assertNotNull(Restaurant::find($rest->id));
    }

    public function testOtherUserCanUpdateAndDeleteForeignPlate_CurrentBehaviour()
    {
        // SEC-4: PlateController@update / @destroy have no ownership check
        $this->actingAs($this->otherUser());
        $plate = $this->ownedPlate();

        $this->patch(route('admin.plates.update', $plate->id), [
            'name' => 'Hijacked plate', 'ingredients' => 'a', 'description' => 'b', 'visible' => 1, 'price' => 1,
        ])->assertRedirect(route('admin.plates.show', $plate->slug));
        $this->assertSame('Hijacked plate', $plate->fresh()->name);

        $this->delete(route('admin.plate.delete', $plate->id))->assertRedirect(route('admin.plates.index', $this->ownedRestaurant()->slug));
        $this->assertNull(Plate::find($plate->id));
    }

    public function testOtherUserCannotUpdateOrDeleteForeignPlate()
    {
        $this->markTestSkipped('SEC-4: PlateController@update/@destroy do not check the owner (separate fix(security) commit).');

        $this->actingAs($this->otherUser());
        $plate = $this->ownedPlate();
        $this->patch(route('admin.plates.update', $plate->id), ['name' => 'Hijacked plate', 'ingredients' => 'a', 'description' => 'b', 'visible' => 1, 'price' => 1])
            ->assertViewIs('admin.notYours.plate');
        $this->delete(route('admin.plate.delete', $plate->id))->assertViewIs('admin.notYours.plate');
        $this->assertNotNull(Plate::find($plate->id));
    }
}
