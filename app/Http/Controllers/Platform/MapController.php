<?php

namespace App\Http\Controllers\Platform;

use App\Analytics\GeoStats;
use App\Analytics\Period;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// tab "Maps" of the platform backoffice: the zone ranking is rendered here, the map
// (Leaflet, loaded only when visible) asks GET /platform/data/map for the same aggregates
class MapController extends Controller
{
    public function index(Request $request): View
    {
        $period = Period::fromRequest($request);

        return view('platform.maps', ['period' => $period, 'map' => GeoStats::map($period)]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json(GeoStats::map(Period::fromRequest($request)));
    }
}
