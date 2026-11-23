<?php

namespace App\Http\Controllers\Platform;

use App\Analytics\Period;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

// tab "Charts" of the platform backoffice (route group /platform: auth + role:admin + can:platform)
class ChartController extends Controller
{
    public function index(Request $request): View
    {
        return view('platform.charts', ['period' => Period::fromRequest($request)]);
    }
}
