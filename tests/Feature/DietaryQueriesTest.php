<?php

namespace Tests\Feature;

use App\Allergen;
use App\DietaryTag;
use App\Ingredient;
use App\Order;
use App\Plate;
use App\Restaurant;
use App\Reviews\RestaurantRating;
use App\Type;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The searches a future assistant will run, on hand-made fixtures (one plate per case) inside a
 * dedicated restaurant, so the result never depends on the generated dataset.
 * Rule under test: only a stated, verified answer matches; missing or unknown information never does.
 */
class DietaryQueriesTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $rest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rest = $this->restaurant('Fixture Kitchen', ['Mexican']);
    }

    private function restaurant(string $name, array $types, int $priceRange = 2): Restaurant
    {
        $r = new Restaurant(['name' => $name, 'phone' => '1', 'city' => 'Milano', 'address' => 'Via Test 1', 'price_range' => $priceRange]);
        $r->user_id = User::firstOrFail()->id;
        $r->slug = Restaurant::uniqueSlug($name);
        $r->save();
        $r->types()->attach(Type::whereIn('name', $types)->pluck('id'));

        return $r;
    }

    // a plate with structured ingredients and only the declarations given
    private function plate(string $name, float $price, array $ingredients, array $opts = [], ?Restaurant $rest = null): Plate
    {
        $rest ??= $this->rest;
        $plate = new Plate([
            'name' => $name, 'description' => 'd', 'ingredients' => implode(', ', $ingredients), 'price' => $price, 'restaurant_id' => $rest->id,
            'course' => $opts['course'] ?? 'main', 'availability' => $opts['availability'] ?? Plate::AVAILABLE,
            'available_from_month' => $opts['from'] ?? null, 'available_to_month' => $opts['to'] ?? null,
            'allergen_info' => $opts['allergen_info'] ?? null, 'serves' => $opts['serves'] ?? 1,
        ]);
        $plate->slug = Plate::uniqueSlug($name, $rest->slug);
        $plate->save();
        $plate->syncIngredients($ingredients, 1);
        $plate->allergens()->sync(collect($opts['allergens'] ?? [])->mapWithKeys(fn ($status, $code) => [Allergen::where('code', $code)->value('id') => ['status' => $status]])->all());
        $plate->dietaryTags()->sync(collect($opts['diet'] ?? [])->mapWithKeys(fn ($status, $code) => [DietaryTag::where('code', $code)->value('id') => ['status' => $status, 'source' => DietaryTag::FROM_RESTAURANT]])->all());

        return $plate;
    }

    private function names($query): array
    {
        return $query->where('restaurant_id', $this->rest->id)->orderBy('name')->pluck('name')->all();
    }

    public function testVeganUnderFifteenEuros()
    {
        $this->plate('A vegan bowl', 12, ['quinoa', 'avocado'], ['diet' => ['vegan' => 'yes']]);
        $this->plate('B vegan but pricey', 18, ['quinoa', 'avocado'], ['diet' => ['vegan' => 'yes']]);
        $this->plate('C not declared', 9, ['quinoa', 'avocado']);
        $this->plate('D not verified', 8, ['quinoa', 'avocado'], ['diet' => ['vegan' => 'unknown']]);
        $this->plate('E not vegan', 7, ['quinoa', 'feta'], ['diet' => ['vegan' => 'no']]);

        $this->assertSame(['A vegan bowl'], $this->names(Plate::withDiet(['vegan'])->where('price', '<', 15)));
    }

    public function testGlutenFreeAndLactoseFreeTogether()
    {
        $this->plate('A both', 10, ['basmati rice', 'chicken'], ['diet' => ['gluten_free' => 'yes', 'lactose_free' => 'yes']]);
        $this->plate('B gluten free only', 10, ['basmati rice', 'cream'], ['diet' => ['gluten_free' => 'yes', 'lactose_free' => 'no']]);
        $this->plate('C one unknown', 10, ['basmati rice', 'chicken'], ['diet' => ['gluten_free' => 'yes', 'lactose_free' => 'unknown']]);
        $this->plate('D nothing declared', 10, ['basmati rice', 'chicken']);

        $this->assertSame(['A both'], $this->names(Plate::withDiet(['gluten_free', 'lactose_free'])));
    }

    public function testFreeFromPeanutsAndSoyNeedsAVerifiedCompleteDeclaration()
    {
        $complete = ['allergen_info' => Plate::ALLERGENS_COMPLETE];
        $this->plate('A safe', 10, ['chicken', 'basmati rice'], $complete);
        $this->plate('B partial declaration', 10, ['chicken', 'basmati rice'], ['allergen_info' => Plate::ALLERGENS_PARTIAL]);
        $this->plate('C cross-contact', 10, ['chicken', 'basmati rice'], [...$complete, 'allergens' => ['peanuts' => 'may_contain']]);
        $this->plate('D declares soy', 10, ['chicken', 'tofu'], [...$complete, 'allergens' => ['soybeans' => 'contains']]);
        // the declaration forgot the soy of the tofu: the ingredients still exclude it
        $this->plate('E forgotten soy', 10, ['chicken', 'tofu'], $complete);
        // an ingredient typed in the admin has no trusted data
        $this->plate('F unverified ingredient', 10, ['chicken', 'grandma secret sauce'], $complete);
        $this->plate('G nothing declared', 10, ['chicken', 'basmati rice']);

        $this->assertSame('unverified', Ingredient::where('name', 'grandma secret sauce')->value('info_status'));
        $this->assertSame(['A safe'], $this->names(Plate::freeFrom(['peanuts', 'soybeans'])));
        $this->assertSame(['soybeans'], Plate::where('name', 'E forgotten soy')->firstOrFail()->derivedAllergens()->pluck('code')->all());
    }

    public function testLowNickelIsNeverImpliedByTheAbsenceOfAllergens()
    {
        $this->plate('A no allergens, no nickel data', 10, ['chicken', 'basmati rice'], ['allergen_info' => Plate::ALLERGENS_COMPLETE]);
        $this->plate('B stated low nickel', 10, ['chicken', 'basmati rice'], ['allergen_info' => Plate::ALLERGENS_COMPLETE, 'diet' => ['low_nickel' => 'yes']]);

        $this->assertSame(['A no allergens, no nickel data'], $this->names(Plate::freeFrom(['gluten', 'milk'])->doesntHave('dietaryTags')));
        $this->assertSame(['B stated low nickel'], $this->names(Plate::withDiet(['low_nickel'])));
    }

    public function testAlternativesWithoutAnIngredient()
    {
        $this->plate('A satay', 10, ['chicken', 'peanut sauce'], ['course' => 'starter']);
        $this->plate('B spring rolls', 8, ['rice paper', 'carrot'], ['course' => 'starter']);
        $this->plate('C peanut noodles', 12, ['rice noodles', 'peanuts'], ['course' => 'main']);

        $this->assertSame(['A satay', 'C peanut noodles'], $this->names(Plate::query()->where(fn ($q) => $q->withIngredients(['peanut sauce'])->orWhere(fn ($q) => $q->withIngredients(['peanuts'])))));
        $this->assertSame(['B spring rolls'], $this->names(Plate::withoutIngredients(['peanuts', 'peanut sauce'])->where('course', 'starter')));
    }

    public function testRestaurantsWithChampagneAboveOneHundredEuros()
    {
        $this->plate('Champagne Brut Réserve NV', 95, ['champagne'], ['course' => 'champagne']);
        $fine = $this->restaurant('Fixture Fine Dining', ['Fine Dining'], 4);
        $this->plate('Champagne Rosé Brut NV', 160, ['champagne'], ['course' => 'champagne'], $fine);

        $ids = Restaurant::whereHas('plates', fn ($q) => $q->where('course', 'champagne')->where('price', '>', 100))->pluck('id');
        $this->assertTrue($ids->contains($fine->id));
        $this->assertFalse($ids->contains($this->rest->id));
    }

    public function testRestaurantsWithAtLeastFiftyOrderablePlates()
    {
        foreach (range(1, 50) as $i) {
            $this->plate("Dish {$i}", 10, ['basmati rice'], ['availability' => $i === 1 ? Plate::SOLD_OUT : Plate::AVAILABLE]);
        }
        $this->plate('Dish 51', 10, ['basmati rice']);

        $count = fn () => Restaurant::withCount(['plates as orderable_count' => fn ($q) => $q->orderable()])->find($this->rest->id)->orderable_count;
        $this->assertSame(50, $count());
        Plate::where('name', 'Dish 51')->where('restaurant_id', $this->rest->id)->firstOrFail()->update(['availability' => Plate::UNAVAILABLE]);
        $this->assertSame(49, $count());
    }

    public function testSeasonalPlatesAreOrderableOnlyInSeason()
    {
        $this->plate('Summer only', 10, ['tomato'], ['from' => 6, 'to' => 9]);
        $this->plate('Winter across the new year', 10, ['cabbage'], ['from' => 11, 'to' => 2]);
        $this->plate('All year', 10, ['potato']);

        $this->assertSame(['All year', 'Summer only'], $this->names(Plate::orderable(7)));
        $this->assertSame(['All year', 'Winter across the new year'], $this->names(Plate::orderable(1)));
        $this->assertFalse(Plate::where('name', 'Summer only')->firstOrFail()->isOrderable(12));
    }

    public function testVegetarianDishesForTwoWithinThirtyEuros()
    {
        $this->plate('A margherita', 9, ['pizza dough', 'mozzarella fior di latte'], ['diet' => ['vegetarian' => 'yes']]);
        $this->plate('B big platter for two', 26, ['mixed leaves', 'feta'], ['diet' => ['vegetarian' => 'yes'], 'serves' => 2]);
        $this->plate('C steak', 14, ['beef'], ['diet' => ['vegetarian' => 'no']]);

        // two portions of a single-serving dish, or one dish for two
        $plates = Plate::withDiet(['vegetarian'])->where('restaurant_id', $this->rest->id)
            ->whereRaw('price * (case when serves >= 2 then 1 else 2 end) <= 30')->orderBy('name')->pluck('name')->all();
        $this->assertSame(['A margherita', 'B big platter for two'], $plates);
    }

    public function testCheapWellRatedMexicanRestaurants()
    {
        $this->rest->reviews()->createMany([['rating' => 5], ['rating' => 4], ['rating' => 5]]);
        $pricey = $this->restaurant('Fixture Mexican Premium', ['Mexican'], 4);
        $pricey->reviews()->createMany([['rating' => 5], ['rating' => 5]]);
        RestaurantRating::refresh($this->rest->id);
        RestaurantRating::refresh($pricey->id);

        // the average is stored on the restaurant: a plain WHERE, no aggregate in the query
        $found = Restaurant::whereHas('types', fn ($q) => $q->where('name', 'Mexican'))
            ->where('price_range', '<=', 2)
            ->where('rating_avg', '>=', 4.5)
            ->pluck('id');
        $this->assertTrue($found->contains($this->rest->id));
        $this->assertFalse($found->contains($pricey->id));
    }

    public function testRestaurantsWithSeveralCuisinesAtOnce()
    {
        $fusion = $this->restaurant('Fixture Sushi Ramen', ['Japanese', 'Sushi', 'Ramen']);
        $sushiOnly = $this->restaurant('Fixture Sushi Only', ['Sushi']);

        $ids = Restaurant::whereHas('types', fn ($q) => $q->where('name', 'Sushi'))
            ->whereHas('types', fn ($q) => $q->where('name', 'Ramen'))->pluck('id');
        $this->assertTrue($ids->contains($fusion->id));
        $this->assertFalse($ids->contains($sushiOnly->id));
    }

    public function testPlatesCompatibleWithSeveralPreferences()
    {
        $this->plate('A vegan and gluten free', 10, ['quinoa'], ['diet' => ['vegan' => 'yes', 'gluten_free' => 'yes']]);
        $this->plate('B vegan only', 10, ['couscous'], ['diet' => ['vegan' => 'yes', 'gluten_free' => 'no']]);
        $this->plate('C gluten free only', 10, ['chicken'], ['diet' => ['vegan' => 'no', 'gluten_free' => 'yes']]);

        $this->assertSame(['A vegan and gluten free'], $this->names(Plate::withDiet(['vegan', 'gluten_free'])));
    }

    public function testMostOrderedPlatesOfACustomer()
    {
        $a = $this->plate('A tacos', 9, ['corn tortilla']);
        $b = $this->plate('B burrito', 10, ['flour tortilla']);
        foreach ([[$a, 2], [$a, 3], [$b, 1]] as [$plate, $qty]) {
            $order = new Order(['restaurant_id' => $this->rest->id, 'price' => $plate->price * $qty, 'customer_name' => 'T', 'customer_lastName' => 'T',
                'customer_email' => 'regular@example.com', 'customer_phone' => '1', 'customer_address' => 'a']);
            $order->status = Order::PAID;
            $order->save();
            $order->plates()->attach($plate->id, ['quantity' => $qty, 'unit_price' => $plate->price]);
        }

        $top = DB::table('order_plate')->join('orders', 'orders.id', '=', 'order_plate.order_id')->join('plates', 'plates.id', '=', 'order_plate.plate_id')
            ->where('orders.customer_email', 'regular@example.com')->where('orders.status', Order::PAID)
            ->groupBy('plates.name')->selectRaw('plates.name, sum(order_plate.quantity) as portions')->orderByDesc('portions')->pluck('portions', 'name')->all();
        $this->assertEquals(['A tacos' => 5, 'B burrito' => 1], $top);
    }

    public function testEditingThePriceKeepsTheOrderHistory()
    {
        $plate = $this->plate('A tacos', 9, ['corn tortilla']);
        $order = new Order(['restaurant_id' => $this->rest->id, 'price' => 18, 'customer_name' => 'T', 'customer_lastName' => 'T',
            'customer_email' => 'h@example.com', 'customer_phone' => '1', 'customer_address' => 'a']);
        $order->save();
        $order->plates()->attach($plate->id, ['quantity' => 2, 'unit_price' => 9]);

        $plate->update(['price' => 12]);
        $plate->delete();

        $line = $order->fresh()->plates->first();
        $this->assertSame('A tacos', $line->name);
        $this->assertEquals(9, $line->pivot->unit_price);
        $this->assertEquals(2, $line->pivot->quantity);
    }
}
