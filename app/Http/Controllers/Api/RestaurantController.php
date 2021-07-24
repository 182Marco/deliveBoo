<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Restaurant;
use App\Type;
use Illuminate\Pagination\LengthAwarePaginator;


class RestaurantController extends Controller
{
    public function restByTypes($ids){
    
    if(!$ids){
        return response()->json();
    }
    $Types_id_array = explode(",",$ids);
    
    // initialized as obj
    $restaurants = [];

     foreach ($Types_id_array as $id) {
        $type = Type::find($id);
        foreach ($type->restaurants as $restaurant) {
            array_push($restaurants, $restaurant);

        }    
    }

    //img
    // if ($restaurant->img) {
    //     $restaurant->img = url('storage/' . $restaurant->img);
    // }

    return  response()->json($restaurants);
    }



    public function restMunu($id)
    {   

        $restaurants = Restaurant::query()->with('plates','types')->get();
        $restaurant = $restaurants->find($id);

      
        return response()->json($restaurant);
    }
}
