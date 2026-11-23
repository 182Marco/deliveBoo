<?php

namespace App\Http\Controllers\Platform;

use App\Analytics\Period;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

// tab "Maps" of the platform backoffice (route group /platform: auth + role:admin + can:platform)
class MapController extends Controller
{
    public function index(Request $request): View
    {
        return view('platform.maps', ['period' => Period::fromRequest($request)]);
    }
}
