<?php

namespace Tests\Feature\Platform;

use App\Analytics\OrderMetrics;
use App\Analytics\Period;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Tab "Maps" of /platform: real coordinates only, aggregates only (step 4.3). */
class MapsTabTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('role', User::ADMIN)->firstOrFail();
    }

    private function range(array $extra = []): string
    {
        return http_build_query([...Period::presets()['all'], ...$extra]);
    }

    public function testThePageHasTheMapBoxAndTheZoneRanking()
    {
        $this->actingAs($this->admin())->get('/platform/maps?'.$this->range())->assertOk()
            ->assertSee('id="platform-map"', false)
            ->assertSee('data-src="'.e(route('platform.data.map', Period::presets()['all'])), false)
            ->assertSee(__('platform.maps.zones.title'))
            ->assertSee(__('platform.maps.missing.none'))
            ->assertSee('platform/maps', false);
    }

    public function testTheDataHasEveryRestaurantWithItsStoredCoordinates()
    {
        $json = $this->actingAs($this->admin())->getJson('/platform/data/map?'.$this->range())->assertOk()->json();

        $this->assertCount(DB::table('restaurants')->whereNotNull('latitude')->count(), $json['restaurants']);
        $first = DB::table('restaurants')->where('id', $json['restaurants'][0]['id'])->first();
        $this->assertSame((float) $first->latitude, $json['restaurants'][0]['lat']);
        $this->assertSame(0, $json['missing']['count']);
        // the zones add up to the platform total
        $range = Period::presets()['all'];
        $this->assertEqualsWithDelta(OrderMetrics::totals(Period::make($range['from'], $range['to']))['gross_value'], array_sum(array_column($json['zones'], 'gross_value')), 0.01);
        // aggregates only: no customer data, no single orders
        $this->assertStringNotContainsString('@', json_encode($json));
        $this->assertArrayNotHasKey('orders', $json);
    }

    public function testARestaurantWithoutCoordinatesIsListedApartNotInvented()
    {
        $restaurant = DB::table('restaurants')->where('city', Period::cities()[0])->first();
        DB::table('restaurants')->where('id', $restaurant->id)->update(['latitude' => null, 'longitude' => null]);

        $json = $this->actingAs($this->admin())->getJson('/platform/data/map?'.$this->range())->json();

        $this->assertNotContains($restaurant->id, array_column($json['restaurants'], 'id'));
        $this->assertSame(1, $json['missing']['count']);
        $this->assertSame([$restaurant->name], $json['missing']['items']);
        // still counted in its zone, whose centre comes only from the located restaurants
        $zone = collect($json['zones'])->firstWhere('zone', $restaurant->city);
        $this->assertSame($zone['restaurants'] - 1, $zone['located']);
        $located = DB::table('restaurants')->where('city', $restaurant->city)->whereNotNull('latitude');
        $this->assertEqualsWithDelta((float) $located->avg('latitude'), $zone['lat'], 0.000001);

        $this->get('/platform/maps?'.$this->range())->assertSee(__('platform.maps.missing.text', ['count' => 1]))->assertSee($restaurant->name);
    }

    public function testCityFilter()
    {
        $city = Period::cities()[0];
        $json = $this->actingAs($this->admin())->getJson('/platform/data/map?'.$this->range(['city' => $city]))->json();

        $this->assertSame([$city], array_values(array_unique(array_column($json['restaurants'], 'city'))));
        $this->assertCount(1, $json['zones']);
    }

    public function testOnlyAdminsGetTheMapData()
    {
        $this->getJson('/platform/data/map')->assertUnauthorized();
        $this->actingAs(User::where('role', User::RESTAURATEUR)->firstOrFail())->getJson('/platform/data/map')->assertForbidden();
    }
}
