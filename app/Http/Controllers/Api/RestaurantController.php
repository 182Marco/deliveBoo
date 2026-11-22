<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Restaurant;
use Illuminate\Http\JsonResponse;

class RestaurantController extends Controller
{
    // the 12 restaurants with the best average rate (a restaurant without rates has average 0)
    public function restTopRated(): JsonResponse
    {
        $top = Restaurant::withAvg('rates as average', 'rate')
            ->orderByDesc('average')
            ->take(12)
            ->get()
            ->each(fn (Restaurant $rest) => $rest->average = (float) $rest->average);

        return response()->json($top);
    }

    // restaurants having at least one of the chosen types, 4 per page
    public function restByTypes(string $ids): JsonResponse
    {
        $rests = Restaurant::whereHas('types', fn ($q) => $q->whereIn('type_id', explode(',', $ids)))
            ->paginate(4);

        return response()->json($rests);
    }

    // restaurant details, average rate, type names and menu (4 plates per page)
    public function restMunu(string $slug): JsonResponse
    {
        $rest = Restaurant::where('slug', $slug)->firstOrFail();

        return response()->json([
            'averageRate' => (float) $rest->rates()->avg('rate'),
            'location' => $rest,
            'types' => $rest->types()->pluck('name'),
            'menu' => $rest->plates()->paginate(4)->through(fn ($p) => $p->setAttribute('restaurant_slug', $rest->slug)),
        ]);
    }
}
