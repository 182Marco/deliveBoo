<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Order;
use Illuminate\Http\Request;

class StatsController extends Controller
{

    public function index()
    {   

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($restaurant_id)
    {

        $related_orders = Order::where('restaurant_id', $restaurant_id)->get();

        return view('admin.stats.show', compact('restaurant_id','related_orders'));
    }

}
