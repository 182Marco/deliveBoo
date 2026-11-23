<?php

namespace Tests\Feature;

use App\Dietary\Derivation;
use App\DietaryTag;
use App\Ingredient;
use App\Plate;
use App\Restaurant;
use App\User;
use Database\Seeders\Support\CatalogPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Runs on the in-memory sqlite database configured in phpunit.xml (SEED_PROFILE=test: the same
 * generator as the development dataset, with a few owners and short menus), never on the
 * development database.
 */
class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    private $tables = [
        'users', 'types', 'restaurants', 'plates', 'orders', 'order_plate', 'restaurant_type', 'rates',
        'ingredients', 'plate_ingredient', 'allergens', 'allergen_ingredient', 'allergen_plate',
        'dietary_tags', 'dietary_tag_plate', 'opening_hours',
    ];

    private const SEEDERS = [
        'AllergensSeeder', 'DietaryTagsSeeder', 'IngredientsSeeder', 'UsersTableSeeder', 'TypesTableSeeder',
        'RestaurantsTableSeeder', 'LegacyRestaurantDetailsSeeder', 'PlatesTableSeeder', 'LegacyPlateDetailsSeeder', 'OwnersSeeder',
        'CatalogRestaurantsSeeder', 'MenusSeeder', 'OrdersTableSeeder', 'OrderHistorySeeder', 'RatesTableSeeder', 'RatingsSeeder',
    ];

    // the shared test database is seeded once (tests/TestCase.php): start these tests from empty
    // tables. RefreshDatabase rolls everything back at the end of each test.
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['order_plate', 'dietary_tag_plate', 'allergen_plate', 'plate_ingredient', 'allergen_ingredient', 'restaurant_type',
                  'opening_hours', 'rates', 'orders', 'plates', 'restaurants', 'ingredients', 'allergens', 'dietary_tags', 'types', 'users'] as $table) {
            DB::table($table)->delete();
        }
    }

    private function counts()
    {
        $counts = [];
        foreach ($this->tables as $table) {
            $counts[$table] = DB::table($table)->count();
        }
        return $counts;
    }

    public function testSeedingTwiceDoesNotDuplicateAnything()
    {
        $this->seed();
        $first = $this->counts();

        foreach ($first as $table => $count) {
            $this->assertGreaterThan(0, $count, "$table is empty after seeding");
        }

        $this->seed();
        $this->assertSame($first, $this->counts());
    }

    public function testEverySeederCanBeRunAloneTwice()
    {
        $this->seed();
        $before = $this->counts();

        foreach (self::SEEDERS as $seeder) {
            $this->seed($seeder);
        }
        $this->assertSame($before, $this->counts());
    }

    public function testSeederFailsClearlyWhenDependenciesAreMissing()
    {
        // plates without restaurants: clear error and nothing written (transaction)
        try {
            $this->seed('PlatesTableSeeder');
            $this->fail('PlatesTableSeeder should fail without restaurants');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('run RestaurantsTableSeeder first', $e->getMessage());
        }
        $this->assertSame(0, DB::table('plates')->count());

        // ingredients without the allergens they refer to
        try {
            $this->seed('IngredientsSeeder');
            $this->fail('IngredientsSeeder should fail without allergens');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('run AllergensSeeder first', $e->getMessage());
        }
        $this->assertSame(0, DB::table('ingredients')->count());
    }

    public function testSeededDataIsConsistent()
    {
        $this->seed();

        // no orphan foreign keys
        $this->assertSame(0, DB::table('restaurants')->leftJoin('users', 'users.id', '=', 'restaurants.user_id')->whereNull('users.id')->count());
        $this->assertSame(0, DB::table('plates')->leftJoin('restaurants', 'restaurants.id', '=', 'plates.restaurant_id')->whereNull('restaurants.id')->count());
        $this->assertSame(0, DB::table('orders')->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')->whereNull('restaurants.id')->count());
        $this->assertSame(0, DB::table('rates')->leftJoin('restaurants', 'restaurants.id', '=', 'rates.restaurant_id')->whereNull('restaurants.id')->count());
        foreach (['plate_ingredient' => 'ingredients', 'allergen_ingredient' => 'ingredients', 'allergen_plate' => 'allergens', 'dietary_tag_plate' => 'dietary_tags'] as $pivot => $table) {
            $fk = $table === 'dietary_tags' ? 'dietary_tag_id' : ($table === 'allergens' ? 'allergen_id' : 'ingredient_id');
            $this->assertSame(0, DB::table($pivot)->leftJoin($table, "{$table}.id", '=', "{$pivot}.{$fk}")->whereNull("{$table}.id")->count(), $pivot);
        }

        // every plate of an order belongs to the restaurant of the order
        $this->assertSame(0, DB::table('order_plate')
            ->join('orders', 'orders.id', '=', 'order_plate.order_id')
            ->join('plates', 'plates.id', '=', 'order_plate.plate_id')
            ->whereColumn('orders.restaurant_id', '!=', 'plates.restaurant_id')
            ->count());

        // no duplicated pivot rows
        foreach (['order_plate' => ['order_id', 'plate_id'], 'restaurant_type' => ['restaurant_id', 'type_id'], 'plate_ingredient' => ['plate_id', 'ingredient_id'],
                  'allergen_plate' => ['plate_id', 'allergen_id'], 'dietary_tag_plate' => ['plate_id', 'dietary_tag_id']] as $pivot => $cols) {
            $this->assertSame(0, DB::table($pivot)->select($cols)->groupBy($cols)->havingRaw('count(*) > 1')->get()->count(), $pivot);
        }

        // every order has lines with a positive quantity, and its price is the sum of quantity x unit price
        $this->assertSame(0, DB::table('order_plate')->where('quantity', '<', 1)->orWhere('unit_price', '<=', 0)->count());
        $sums = DB::table('order_plate')->selectRaw('order_id, sum(quantity * unit_price) as total')->groupBy('order_id')->pluck('total', 'order_id');
        foreach (DB::table('orders')->get(['id', 'price']) as $order) {
            $this->assertArrayHasKey($order->id, $sums->all(), "order {$order->id} has no plates");
            $this->assertEqualsWithDelta($sums[$order->id], $order->price, 0.01, "order {$order->id} price");
        }

        // slugs, names and prices are valid; slugs and names unique where required
        $this->assertSame(0, DB::table('plates')->where('name', '')->orWhere('slug', '')->orWhere('price', '<=', 0)->count());
        $this->assertSame(0, DB::table('restaurants')->where('slug', '')->count());
        $this->assertSame(DB::table('plates')->count(), DB::table('plates')->distinct()->count('slug'));
        $this->assertSame(DB::table('restaurants')->count(), DB::table('restaurants')->distinct()->count('slug'));
        $this->assertSame(DB::table('users')->count(), DB::table('users')->distinct()->count('email'));
        // only restaurateurs have a VAT number (customers and admins: NULL), one per account
        $this->assertSame(DB::table('users')->where('role', User::RESTAURATEUR)->count(), DB::table('users')->distinct()->count('vat_number'));
        $this->assertSame(0, DB::table('users')->where('role', User::RESTAURATEUR)->whereNull('vat_number')->count());
        $this->assertSame(DB::table('types')->count(), DB::table('types')->distinct()->count('name'));
        $this->assertSame(0, DB::table('plates')->select('restaurant_id', 'name')->groupBy('restaurant_id', 'name')->havingRaw('count(*) > 1')->get()->count());

        // every plate has at least one ingredient, every owner at least one restaurant
        $this->assertSame(0, DB::table('plates')->whereNotExists(fn ($q) => $q->from('plate_ingredient')->whereColumn('plate_ingredient.plate_id', 'plates.id'))->count());
        $this->assertSame(0, User::where('role', User::RESTAURATEUR)->doesntHave('restaurants')->count());
        $this->assertSame(0, User::where('role', '!=', User::RESTAURATEUR)->has('restaurants')->count());

        // every restaurant has at least one plate, one type and one rate
        foreach (DB::table('restaurants')->get() as $restaurant) {
            $this->assertGreaterThan(0, DB::table('plates')->where('restaurant_id', $restaurant->id)->count(), "{$restaurant->name} has no plates");
            $this->assertGreaterThan(0, DB::table('restaurant_type')->where('restaurant_id', $restaurant->id)->count(), "{$restaurant->name} has no types");
            $this->assertGreaterThan(0, DB::table('rates')->where('restaurant_id', $restaurant->id)->count(), "{$restaurant->name} has no rates");
        }

        // every restaurant, the historic ones too, has a segment, a description and opening hours
        $this->assertSame(0, DB::table('restaurants')->whereNull('segment')->orWhereNull('description')->orWhereNull('price_range')->count());
        $this->assertSame(0, DB::table('restaurants')->whereNotExists(fn ($q) => $q->from('opening_hours')->whereColumn('opening_hours.restaurant_id', 'restaurants.id'))->count());

        // "visible" always follows availability
        $this->assertSame(0, DB::table('plates')->whereRaw("(availability = 'available') <> (visible = 1)")->count());
    }

    public function testTheDemoAccountKeepsItsRestaurantsAndGetsItsGroup()
    {
        $this->seed();
        $romiti = User::where('email', CatalogPlan::ROMITI)->firstOrFail();

        // the historic restaurants are untouched: same owner and slug
        foreach (['the-garage', 'burger-king', 'mc-donalds'] as $slug) {
            $this->assertSame($romiti->id, Restaurant::where('slug', $slug)->value('user_id'), $slug);
        }
        $this->assertSame(3 + CatalogPlan::profile()['romiti_new'], $romiti->restaurants()->count());
    }

    public function testDeclarationsNeverClaimMoreThanTheData()
    {
        $this->seed();

        // a "yes" derived from the ingredients needs every ingredient verified
        $derivedYes = DB::table('dietary_tag_plate')->where('source', DietaryTag::FROM_INGREDIENTS)->where('status', DietaryTag::YES)->pluck('plate_id')->unique();
        $this->assertSame(0, DB::table('plate_ingredient')->join('ingredients', 'ingredients.id', '=', 'plate_ingredient.ingredient_id')
            ->whereIn('plate_ingredient.plate_id', $derivedYes)->where('ingredients.info_status', '!=', Ingredient::VERIFIED)->count());

        // low nickel "yes" only when every ingredient is verified and low (indicative data)
        $lowNickel = Plate::whereHas('dietaryTags', fn ($q) => $q->where('code', 'low_nickel')->where('dietary_tag_plate.status', DietaryTag::YES))->with('ingredientList')->get();
        foreach ($lowNickel as $plate) {
            $this->assertSame(['low'], $plate->ingredientList->pluck('nickel_level')->unique()->values()->all(), $plate->name);
        }

        // a plate that is "free from" an allergen never contains it, by declaration or by ingredients
        foreach (['gluten', 'milk', 'eggs', 'nuts', 'soybeans'] as $code) {
            $derived = DB::table('plate_ingredient')->join('allergen_ingredient', 'allergen_ingredient.ingredient_id', '=', 'plate_ingredient.ingredient_id')
                ->join('allergens', 'allergens.id', '=', 'allergen_ingredient.allergen_id')->where('allergens.code', $code)->pluck('plate_ingredient.plate_id');
            $this->assertSame([], Plate::freeFrom([$code])->whereIn('id', $derived)->pluck('name')->all(), $code);
        }

        // the derived rules give the same answer as the stored rows of a complete declaration
        $plate = Plate::where('allergen_info', Plate::ALLERGENS_COMPLETE)->has('dietaryTags')->with('ingredientList.allergens', 'allergens', 'dietaryTags')->firstOrFail();
        $expected = Derivation::derive(
            $plate->ingredientList->map(fn ($i) => [...$i->only(['origin', 'category', 'contains_lactose', 'nickel_level', 'info_status']), 'allergens' => $i->allergens->pluck('code')->all()])->all(),
            $plate->allergen_info,
            $plate->allergens->mapWithKeys(fn ($a) => [$a->code => $a->pivot->status])->all(),
        );
        foreach ($plate->dietaryTags->where('pivot.source', DietaryTag::FROM_INGREDIENTS) as $tag) {
            $this->assertSame($expected[$tag->code], $tag->pivot->status, $tag->code);
        }
    }
}
