<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Restaurant;
use App\Type;


class RestaurantController extends Controller
{
    public function restByTypes($ids){
    
    if(!$ids){
        return response()->json();
    }
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



    public function restMunu($id)
    {   

        $restaurants = Restaurant::query()->with('plates','types')->get();
        $restaurant = $restaurants->find($id);

      
        return response()->json($restaurant);
    }
}
