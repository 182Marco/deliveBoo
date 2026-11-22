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
        $this->post(route('admin.plates.store'), ['name' => str_repeat('a', 81)])
            ->assertSessionHasErrors([
                'name' => 'you have exceeded the 80 characters allowed for the name',
                'ingredients' => 'Required Field!',
                'description' => 'Required Field!',
                'availability' => 'Required Field!',
                'price' => 'Required Field!',
            ]);

        $this->post(route('admin.plates.store'), $this->plateData(['img' => UploadedFile::fake()->create('a.pdf', 5, 'application/pdf')]))
            ->assertSessionHasErrors('img');
    }

    public function testUpdatePlateKeepsSlugAndChecksUniqueNameInsideTheRestaurant()
    {
        $plate = $this->ownedPlate();
        $sameMenu = Plate::where('restaurant_id', $plate->restaurant_id)->where('id', '!=', $plate->id)->firstOrFail();
        $otherMenu = Plate::where('restaurant_id', '!=', $plate->restaurant_id)->firstOrFail();

        $this->patch(route('admin.plates.update', $plate->id), $this->plateData(['name' => 'Renamed', 'price' => 3]))
            ->assertRedirect(route('admin.plates.show', $plate->slug));
        $this->assertSame('Renamed', $plate->fresh()->name);
        $this->assertSame($plate->slug, $plate->fresh()->slug);

        // the same dish name can exist in another restaurant, not twice in the same menu
        $this->patch(route('admin.plates.update', $plate->id), $this->plateData(['name' => $otherMenu->name]))
            ->assertSessionHasNoErrors();
        $this->patch(route('admin.plates.update', $plate->id), $this->plateData(['name' => $sameMenu->name]))
            ->assertSessionHasErrors(['name' => 'This name is already used']);
    }

    public function testStorePlateWithANameUsedElsewhereGetsAUniqueSlug()
    {
        $existing = Plate::where('restaurant_id', '!=', $this->ownedRestaurant()->id)->firstOrFail();

        $this->post(route('admin.plates.store'), $this->plateData(['name' => $existing->name]))->assertRedirect();

        $new = Plate::where('restaurant_id', $this->ownedRestaurant()->id)->where('name', $existing->name)->firstOrFail();
        $this->assertNotSame($existing->slug, $new->slug);
    }

    public function testStorePlateSavesStructuredIngredientsAndOnlyTheDeclaredDietaryInfo()
    {
        $this->post(route('admin.plates.store'), $this->plateData([
            'name' => 'House Salad', 'ingredients' => 'Romaine lettuce, Smoked yuzu dust , romaine lettuce',
            'visible' => null, 'availability' => 'sold_out', 'course' => 'salad',
            'diet' => ['vegan' => 'yes', 'halal' => '', 'spicy' => 'unknown'],
            'allergen_info' => '', 'allergens' => ['mustard' => 'may_contain', 'eggs' => ''],
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $plate = Plate::where('name', 'House Salad')->firstOrFail();
        $this->assertSame(['romaine lettuce', 'smoked yuzu dust'], $plate->ingredientList->pluck('name')->all());
        $this->assertSame('Romaine lettuce, Smoked yuzu dust', $plate->ingredients);
        // an ingredient typed in the admin has no trusted reference data
        $this->assertSame('unverified', \App\Ingredient::where('name', 'smoked yuzu dust')->value('info_status'));
        $this->assertSame('sold_out', $plate->availability);
        $this->assertFalse($plate->visible);

        // "Not declared" writes nothing: only vegan (yes) and spicy (not verified) exist
        $this->assertEqualsCanonicalizing(['vegan' => 'yes', 'spicy' => 'unknown'], $plate->dietaryTags->mapWithKeys(fn ($t) => [$t->code => $t->pivot->status])->all());
        $this->assertSame(['mustard' => 'may_contain'], $plate->allergens->mapWithKeys(fn ($a) => [$a->code => $a->pivot->status])->all());
        $this->assertNull($plate->allergen_info);
        // nothing is derived while an ingredient is unverified
        $this->assertSame(0, $plate->dietaryTags()->wherePivot('source', 'ingredients')->count());
    }

    public function testAPlatePhotoCanBeALinkToAnImageHostedElsewhere()
    {
        $link = 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=480';
        $this->post(route('admin.plates.store'), $this->plateData(['name' => 'Linked Photo', 'img_url' => $link]))->assertSessionHasNoErrors();
        $plate = Plate::where('name', 'Linked Photo')->firstOrFail();
        $this->assertSame($link, $plate->img);

        // only https links; an upload replaces the link
        $this->patch(route('admin.plates.update', $plate->id), $this->plateData(['name' => 'Linked Photo', 'img_url' => 'http://example.com/a.jpg']))
            ->assertSessionHasErrors(['img_url' => 'Use a full https:// link to an image.']);
        $this->patch(route('admin.plates.update', $plate->id), $this->plateData(['name' => 'Linked Photo', 'img' => $this->png('p.png')]))->assertSessionHasNoErrors();
        $this->assertStringStartsWith('plates-imgs/', $plate->fresh()->img);

        // and a link replaces the upload, whose file is deleted
        $uploaded = $plate->fresh()->img;
        $this->patch(route('admin.plates.update', $plate->id), $this->plateData(['name' => 'Linked Photo', 'img_url' => $link]))->assertSessionHasNoErrors();
        $this->assertSame($link, $plate->fresh()->img);
        Storage::assertMissing($uploaded);

        // the edit form shows the current link
        $this->get(route('admin.plates.edit', $plate->slug))->assertOk()->assertSee($link, false);
    }

    public function testStorePlateWithoutAnyDietaryInfoWritesNoDeclaration()
    {
        $this->post(route('admin.plates.store'), $this->plateData(['name' => 'Plain Dish']))->assertSessionHasNoErrors();

        $plate = Plate::where('name', 'Plain Dish')->firstOrFail();
        $this->assertSame(0, $plate->allergens()->count());
        $this->assertSame(0, $plate->dietaryTags()->wherePivot('source', 'restaurant')->count());
        $this->assertNull($plate->allergen_info);
        $this->assertGreaterThan(0, $plate->ingredientList()->count());
    }

    public function testDestroyPlateIsSoftAndKeepsTheOrderHistory()
    {
        $plate = Plate::has('orders')->where('restaurant_id', $this->ownedRestaurant()->id)->firstOrFail();
        $lines = \DB::table('order_plate')->where('plate_id', $plate->id)->count();

        $this->delete(route('admin.plate.delete', $plate->id))
            ->assertRedirect(route('admin.plates.index', $this->ownedRestaurant()->slug))
            ->assertSessionHas('deleted', $plate->name);

        $this->assertNull(Plate::find($plate->id));
        $this->assertSoftDeleted($plate);
        $this->assertSame($lines, \DB::table('order_plate')->where('plate_id', $plate->id)->count());
        $order = \App\Order::whereHas('plates', fn ($q) => $q->where('plates.id', $plate->id))->firstOrFail();
        $this->assertTrue($order->plates->contains('id', $plate->id));
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
