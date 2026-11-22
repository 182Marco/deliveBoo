<?php

namespace Tests\Feature\Admin;

use App\Plate;
use App\Restaurant;
use App\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\SeededFixtures;
use Tests\TestCase;

/** Characterization of the admin create / update / delete flows done by the owner. */
class CrudTest extends TestCase
{
    use RefreshDatabase, SeededFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake();
        $this->actingAs($this->owner());
    }

    /** a real 1x1 PNG: unlike UploadedFile::fake()->image() it does not need the GD extension */
    private function png($name)
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
        ));
    }

    // ---------------------------------------------------------------- restaurants

    public function testStoreRestaurantWithImageAndTypes()
    {
        $types = Type::whereIn('name', ['Vegan', 'Italian'])->pluck('id')->all();

        $response = $this->post(route('admin.restaurants.store'), [
            'name' => 'New Place', 'phone' => '333', 'city' => 'Torino', 'address' => 'via Po 1',
            'types' => $types, 'img' => $this->png('cover.png'),
        ]);

        $rest = Restaurant::where('name', 'New Place')->firstOrFail();
        $this->assertSame('new-place', $rest->slug);
        $response->assertRedirect(route('admin.restaurants.show', $rest->id));
        $this->assertEquals($this->owner()->id, $rest->user_id);
        $this->assertStringStartsWith('restaurants-imgs/', $rest->img);
        Storage::assertExists($rest->img);
        $this->assertEquals(collect($types)->sort()->values()->all(), $rest->types()->pluck('types.id')->sort()->values()->all());
    }

    public function testStoreRestaurantGetsAUniqueSlug()
    {
        // a name with the same slug as an existing restaurant ("Il cacciatore" -> il-cacciatore)
        $this->post(route('admin.restaurants.store'), [
            'name' => 'Il Cacciatore!', 'phone' => '333', 'city' => 'Torino', 'address' => 'via Po 1',
        ])->assertRedirect();

        $this->assertSame('il-cacciatore-2', Restaurant::where('name', 'Il Cacciatore!')->value('slug'));
    }

    public function testStoreRestaurantValidationMessages()
    {
        $this->post(route('admin.restaurants.store'), ['name' => str_repeat('a', 81)])
            ->assertSessionHasErrors([
                'name' => 'Max 80 characters allowed for the name',
                'phone' => 'The phone is required!',
                'city' => 'The city is required!',
                'address' => 'The address is required!',
            ]);

        $this->post(route('admin.restaurants.store'), ['name' => 'x', 'phone' => '1', 'city' => 'c', 'address' => 'a',
            'img' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('img');
    }

    public function testUpdateRestaurantReplacesImageAndSyncsOrDetachesTypes()
    {
        $rest = $this->ownedRestaurant();
        $vegan = Type::where('name', 'Vegan')->value('id');

        $this->patch(route('admin.restaurants.update', $rest->id), [
            'name' => $rest->name, 'phone' => '999', 'city' => 'Bologna', 'address' => 'via Indipendenza 2',
            'types' => [$vegan], 'img' => $this->png('new.png'),
        ])->assertRedirect(route('admin.restaurants.show', $rest->id));

        $rest->refresh();
        $this->assertSame('Bologna', $rest->city);
        $this->assertEquals([$vegan], $rest->types()->pluck('types.id')->all());
        Storage::assertExists($rest->img);

        $this->patch(route('admin.restaurants.update', $rest->id), [
            'name' => $rest->name, 'phone' => '999', 'city' => 'Bologna', 'address' => 'x',
        ]);
        $this->assertSame(0, $rest->types()->count());
    }

    public function testUpdateRestaurantNameMustBeUnique()
    {
        $this->patch(route('admin.restaurants.update', $this->ownedRestaurant()->id), [
            'name' => 'Burger King', 'phone' => '1', 'city' => 'c', 'address' => 'a',
        ])->assertSessionHasErrors(['name' => 'This name is already used']);
    }

    public function testDestroyRestaurantDetachesTypesAndFlashesName()
    {
        $rest = $this->ownedRestaurant();

        $this->delete(route('admin.restaurants.destroy', $rest->id))
            ->assertRedirect(route('admin.restaurants.index'))
            ->assertSessionHas('deleted', "Mc Donald's");

        $this->assertNull(Restaurant::find($rest->id));
        $this->assertSame(0, \DB::table('restaurant_type')->where('restaurant_id', $rest->id)->count());
    }

    // ---------------------------------------------------------------- plates

    private function plateData(array $override = [])
    {
        return array_merge([
            'name' => 'Pizza Test', 'ingredients' => 'tomato', 'description' => 'good',
            'visible' => 1, 'price' => 9.5, 'restaurant_id' => $this->ownedRestaurant()->id,
        ], $override);
    }

    public function testStorePlateWithImageCreatesSlug()
    {
        $this->post(route('admin.plates.store'), $this->plateData(['img' => $this->png('p.png')]))
            ->assertRedirect(route('admin.plates.show', 'pizza-test'));

        $plate = Plate::where('slug', 'pizza-test')->firstOrFail();
        $this->assertSame('Pizza Test', $plate->name);
        $this->assertEquals(9.5, $plate->price);
        $this->assertStringStartsWith('plates-imgs/', $plate->img);
        Storage::assertExists($plate->img);
    }

    public function testStorePlateValidationMessages()
    {
        $this->post(route('admin.plates.store'), ['name' => str_repeat('a', 51)])
            ->assertSessionHasErrors([
                'name' => 'you have exceeded the 50 characters allowed for the name',
                'ingredients' => 'Required Field!',
                'description' => 'Required Field!',
                'visible' => 'Required Field!',
                'price' => 'Required Field!',
            ]);

        $this->post(route('admin.plates.store'), $this->plateData(['img' => UploadedFile::fake()->create('a.pdf', 5, 'application/pdf')]))
            ->assertSessionHasErrors('img');
    }

    public function testUpdatePlateKeepsSlugAndChecksUniqueName()
    {
        $plate = $this->ownedPlate();
        $other = Plate::where('id', '!=', $plate->id)->firstOrFail();

        $this->patch(route('admin.plates.update', $plate->id), $this->plateData(['name' => 'Renamed', 'price' => 3]))
            ->assertRedirect(route('admin.plates.show', $plate->slug));
        $this->assertSame('Renamed', $plate->fresh()->name);
        $this->assertSame($plate->slug, $plate->fresh()->slug);

        $this->patch(route('admin.plates.update', $plate->id), $this->plateData(['name' => $other->name]))
            ->assertSessionHasErrors(['name' => 'This name is already used']);
    }

    public function testDestroyPlateDetachesOrdersAndRedirectsToRestaurantPlates()
    {
        $plate = Plate::has('orders')->where('restaurant_id', $this->ownedRestaurant()->id)->firstOrFail();

        $this->delete(route('admin.plate.delete', $plate->id))
            ->assertRedirect(route('admin.plates.index', $this->ownedRestaurant()->slug))
            ->assertSessionHas('deleted', $plate->name);

        $this->assertNull(Plate::find($plate->id));
        $this->assertSame(0, \DB::table('order_plate')->where('plate_id', $plate->id)->count());
    }

    public function testPaginationLinksUseFoodsyMarkup()
    {
        // 5 per page on the restaurant index: federico owns 3, add more to get a second page
        foreach (range(1, 4) as $i) {
            $r = new Restaurant(['name' => "Extra $i", 'phone' => '1', 'city' => 'c', 'address' => 'a']);
            $r->user_id = $this->owner()->id;
            $r->slug = Str::slug("Extra $i");
            $r->save();
        }

        $this->get(route('admin.restaurants.index'))->assertOk()->assertSee('ui-pagination', false)->assertSee('ui-page', false);
    }
}
