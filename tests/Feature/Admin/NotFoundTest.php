<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** Missing records in the admin area answer 404 (they used to be 500 errors). */
class NotFoundTest extends TestCase
{
    use RefreshDatabase, SeededFixtures;

    public function testMissingRecordsAre404()
    {
        $this->actingAs($this->owner());

        foreach ([
            ['get', route('admin.restaurants.show', 999999)],
            ['get', route('admin.restaurants.edit', 999999)],
            ['delete', route('admin.restaurants.destroy', 999999)],
            ['get', route('admin.plates.index', 'inesistente')],
            ['get', route('admin.plates.create', 'inesistente')],
            ['get', route('admin.plates.show', 'inesistente')],
            ['get', route('admin.plates.edit', 'inesistente')],
            ['delete', route('admin.plate.delete', 999999)],
            ['get', route('admin.orders.index', 'inesistente')],
            ['get', route('admin.orders.show', 999999)],
        ] as [$method, $url]) {
            $this->$method($url)->assertNotFound();
        }

        $this->patch(route('admin.plates.update', 999999), [
            'name' => 'x', 'ingredients' => 'a', 'description' => 'b', 'visible' => 1, 'price' => 1,
        ])->assertNotFound();
        $this->patch(route('admin.restaurants.update', 999999), [
            'name' => 'x', 'phone' => '1', 'city' => 'c', 'address' => 'a',
        ])->assertNotFound();
    }
}
