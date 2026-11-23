<?php

namespace App\Http\Controllers\Platform;

use App\Analytics\Period;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

// tab "Tables" of the platform backoffice: one sub-tab per data set (whitelisted in the route)
class TableController extends Controller
{
    public const SECTIONS = ['restaurants', 'orders', 'customers', 'zones', 'periods'];

    public function index(Request $request, string $section = 'restaurants'): View
    {
        return view('platform.tables', ['period' => Period::fromRequest($request), 'section' => $section, 'sections' => self::SECTIONS]);
    }
}
