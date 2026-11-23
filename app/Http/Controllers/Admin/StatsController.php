<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Order;
use App\Restaurant;
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

        // SEC-2: orders carry customer data, only the owner reads them
        $restaurant = Restaurant::findOrFail($restaurant_id);
        if (auth()->user()->cannot('manage', $restaurant)) {
            return view('admin.notYours.orders');
        }

        $related_orders = Order::paid()->where('restaurant_id', $restaurant_id)->get();

        return view('admin.stats.show', compact('restaurant_id','related_orders'));
    }

}
