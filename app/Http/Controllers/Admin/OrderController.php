<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Order;
use App\Restaurant;

class OrderController extends Controller
{
    // orders of a restaurant (by slug), newest first, only for its owner
    public function index($slug)
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        if ($restaurant->user_id != auth()->id()) {
            return view('admin.notYours.orders');
        }

        $orders = $restaurant->orders()->orderBy('created_at', 'DESC')->paginate(5);
        $restaurant_id = $restaurant->id;

        return view('admin.orders.index', compact('orders', 'restaurant_id'));
    }

    public function show($id)
    {
        $data = Order::with('plates', 'restaurant')->findOrFail($id);

        if ($data->restaurant?->user_id != auth()->id()) {
            return view('admin.notYours.plate');
        }

        return view('admin.orders.show', compact('data'));
    }
}
