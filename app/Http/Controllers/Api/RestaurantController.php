<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Plate;
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

    // restaurant details, average rate, type names and menu (4 plates per page, by course).
    // Every plate also says whether it can be ordered now and lists its structured ingredients.
    public function restMunu(string $slug): JsonResponse
    {
        $rest = Restaurant::where('slug', $slug)->firstOrFail();

        $menu = $rest->plates()
            ->with('ingredientList:id,name')
            ->orderByRaw('case when course is null then 1 else 0 end')
            ->orderByRaw($this->courseOrderSql())
            ->orderBy('id')
            ->paginate(4)
            ->through(fn (Plate $p) => $p->setAttribute('restaurant_slug', $rest->slug)
                ->setAttribute('orderable', $p->isOrderable())
                ->setAttribute('ingredient_list', $p->ingredientList->pluck('name'))
                ->makeHidden('ingredientList'));

        return response()->json([
            'averageRate' => (float) $rest->rates()->avg('rate'),
            'location' => $rest,
            'types' => $rest->types()->pluck('name'),
            'menu' => $menu,
        ]);
    }

    // menu order of the courses (Plate::COURSES), portable between MySQL and SQLite
    private function courseOrderSql(): string
    {
        $cases = collect(Plate::COURSES)->map(fn ($course, $i) => "when '{$course}' then {$i}")->implode(' ');

        return "case course {$cases} else 999 end";
    }
}
