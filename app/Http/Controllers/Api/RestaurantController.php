<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Type;


class RestaurantController extends Controller
{
    public function index($ids){
    
    $Types_id_array = explode(",",$ids);
    
    $restaurants = [];

     foreach ($Types_id_array as $id) {
        $type = Type::find($id);
        foreach ($type->restaurants as $restaurant) {
            array_push($restaurants, $restaurant);
        }
    }

    return  response()->json($restaurants);
    }
}
