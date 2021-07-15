<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Restaurant;

class RestaurantController extends Controller
{
    //temporarily Get all Restaurant
    public function index(){
        $restaurants=Restaurant::all();

        return response()->json($restaurants);
    }
}
