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
 * SEC-1..SEC-4 (dev/PIANO_MIGRAZIONE_L13.md) are fixed by App\Policies\RestaurantPolicy and
 * PlatePolicy: the testOtherUserCannot* tests pin down the secure behaviour.
 */
class OwnershipTest extends TestCase
{
    use RefreshDatabase, SeededFixtures;

    protected function setUp(): void
    {
        parent::setUp();
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

    public function testOtherUserCannotStorePlateInForeignRestaurant()
    {
        $this->actingAs($this->otherUser());
        $this->post(route('admin.plates.store'), $this->platePayload($this->ownedRestaurant()->id, 'Intruder Plate'))
            ->assertViewIs('admin.notYours.plate');
        $this->assertSame(0, Plate::where('slug', 'intruder-plate')->count());
    }

    public function testStorePlateNeedsAnExistingRestaurant()
    {
        $this->actingAs($this->owner());

        $this->post(route('admin.plates.store'), $this->platePayload(null, 'Orphan Plate'))->assertSessionHasErrors('restaurant_id');
        $this->post(route('admin.plates.store'), $this->platePayload(999999, 'Orphan Plate'))->assertViewIs('admin.notYours.plate');
        $this->assertSame(0, Plate::where('slug', 'orphan-plate')->count());
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

    public function testOtherUserCannotSeeForeignStats()
    {
        $this->actingAs($this->otherUser());
        $this->get(route('admin.stats.show', $this->ownedRestaurant()->id))->assertViewIs('admin.notYours.orders');
    }

    public function testGuestCannotSeeStats()
    {
        $this->get(route('admin.stats.show', $this->ownedRestaurant()->id))->assertRedirect('/login');
    }

    // ---------------------------------------------------------------- update / destroy (SEC-3, SEC-4)

    public function testOtherUserCannotUpdateOrDeleteForeignRestaurant()
    {
        $this->actingAs($this->otherUser());
        $rest = $this->ownedRestaurant();
        $this->patch(route('admin.restaurants.update', $rest->id), ['name' => 'Hijacked', 'phone' => '1', 'city' => 'X', 'address' => 'Y'])
            ->assertViewIs('admin.notYours.restaurant');
        $this->delete(route('admin.restaurants.destroy', $rest->id))->assertViewIs('admin.notYours.restaurant');
        $this->assertNotNull(Restaurant::find($rest->id));
        $this->assertNotSame('Hijacked', $rest->fresh()->name);
    }

    public function testOwnerCanUpdateOwnRestaurant()
    {
        $this->actingAs($this->owner());
        $rest = $this->ownedRestaurant();

        $this->patch(route('admin.restaurants.update', $rest->id), ['name' => 'Renamed by owner', 'phone' => '1', 'city' => 'X', 'address' => 'Y'])
            ->assertRedirect(route('admin.restaurants.show', $rest->id));
        $this->assertSame('Renamed by owner', $rest->fresh()->name);
    }

    public function testOtherUserCannotUpdateOrDeleteForeignPlate()
    {
        $this->actingAs($this->otherUser());
        $plate = $this->ownedPlate();
        $this->patch(route('admin.plates.update', $plate->id), ['name' => 'Hijacked plate', 'ingredients' => 'a', 'description' => 'b', 'visible' => 1, 'price' => 1])
            ->assertViewIs('admin.notYours.plate');
        $this->delete(route('admin.plate.delete', $plate->id))->assertViewIs('admin.notYours.plate');
        $this->assertNotNull(Plate::find($plate->id));
        $this->assertNotSame('Hijacked plate', $plate->fresh()->name);
    }

    public function testOwnerCanUpdateAndDeleteOwnPlate()
    {
        $this->actingAs($this->owner());
        $plate = $this->ownedPlate();

        $this->patch(route('admin.plates.update', $plate->id), ['name' => 'Owner edit', 'ingredients' => 'a', 'description' => 'b', 'visible' => 1, 'price' => 1])
            ->assertRedirect(route('admin.plates.show', $plate->slug));
        $this->assertSame('Owner edit', $plate->fresh()->name);

        $this->delete(route('admin.plate.delete', $plate->id))->assertRedirect(route('admin.plates.index', $this->ownedRestaurant()->slug));
        $this->assertNull(Plate::find($plate->id));
    }
}
