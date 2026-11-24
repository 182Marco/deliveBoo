<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Every named route keeps its URI and HTTP method across the migration
 * (Blade views and the SPA call them by name / by path).
 */
class RoutesTest extends TestCase
{
    /** [name, params, uri, method] */
    private function routes()
    {
        return [
            ['login', [], '/login', 'GET'],
            ['logout', [], '/logout', 'POST'],
            ['register', [], '/register', 'GET'],
            ['password.request', [], '/password/reset', 'GET'],
            ['password.email', [], '/password/email', 'POST'],
            ['password.update', [], '/password/reset', 'POST'],
            ['password.confirm', [], '/password/confirm', 'GET'],
            ['admin.home', [], '/admin', 'GET'],
            ['admin.restaurants.index', [], '/admin/restaurants', 'GET'],
            ['admin.restaurants.create', [], '/admin/restaurants/create', 'GET'],
            ['admin.restaurants.store', [], '/admin/restaurants', 'POST'],
            ['admin.restaurants.show', [7], '/admin/restaurants/7', 'GET'],
            ['admin.restaurants.edit', [7], '/admin/restaurants/7/edit', 'GET'],
            ['admin.restaurants.update', [7], '/admin/restaurants/7', 'PATCH'],
            ['admin.restaurants.destroy', [7], '/admin/restaurants/7', 'DELETE'],
            ['admin.plates.index', ['abc'], '/admin/plates/abc', 'GET'],
            ['admin.plates.create', ['abc'], '/admin/plate/create/abc', 'GET'],
            ['admin.plates.edit', ['abc'], '/admin/plates/edit/abc', 'GET'],
            ['admin.plates.update', [3], '/admin/plates/update/3', 'PATCH'],
            ['admin.plates.store', [], '/admin/plate/store', 'POST'],
            ['admin.plates.show', ['abc'], '/admin/plate/show/abc', 'GET'],
            ['admin.plate.delete', [3], '/admin/plate/delete/3', 'DELETE'],
            ['admin.orders.index', ['abc'], '/admin/orders/abc', 'GET'],
            ['admin.orders.show', [5], '/admin/orders/show/5', 'GET'],
            ['admin.stats.index', [], '/admin/stats', 'GET'],
            ['admin.stats.show', [5], '/admin/stats/5', 'GET'],
            ['admin.reviews.index', ['abc'], '/admin/reviews/abc', 'GET'],
            ['admin.reviews.reply', [9], '/admin/reviews/reply/9', 'PUT'],
            ['admin.reviews.reply.delete', [9], '/admin/reviews/reply/9', 'DELETE'],
            ['platform.reviews.hide', [9], '/platform/reviews/9/hide', 'POST'],
            ['platform.reviews.unhide', [9], '/platform/reviews/9/unhide', 'POST'],
            ['locale', ['de'], '/locale/de', 'GET'],
        ];
    }

    public function testNamedRoutesKeepUriAndMethod()
    {
        foreach ($this->routes() as [$name, $params, $uri, $method]) {
            $this->assertTrue(Route::has($name), "route $name missing");
            $this->assertSame(url($uri), route($name, $params), $name);
            $this->assertContains($method, Route::getRoutes()->getByName($name)->methods(), $name);
        }
    }

    public function testApiPathsExist()
    {
        $paths = collect(Route::getRoutes()->getRoutes())->map(function ($r) {
            return implode('|', array_diff($r->methods(), ['HEAD'])) . ' ' . $r->uri();
        })->all();

        foreach ([
            'GET api/types',
            'GET api/restaurantsTopRated',
            'GET api/restaurantsSearch',
            'GET api/restaurants/{ids}',
            'GET api/restaurantsMenu/{slug}',
            'POST api/orders',
            'GET api/restaurants/{slug}/reviews',
            'PUT api/me/orders/{order}/review',
        ] as $expected) {
            $this->assertContains($expected, $paths);
        }
    }

    public function testCatchAllServesTheSpa()
    {
        $this->get('/')->assertOk()->assertViewIs('guest.home');
        $this->get('/qualsiasi/percorso/della-spa')->assertOk()->assertViewIs('guest.home');
        $this->get('/well-done!Successfull-payment!Your-order-has-been-confirmed')->assertOk()->assertViewIs('guest.home');
    }
}
