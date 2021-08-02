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


    $req = explode(",",$ids);

    /* chiamata a db con relazione */
    $rests = Restaurant::whereHas('types', function($q) use($req) {
        $q->whereIn('type_id', $req);
    })->paginate(4);

    
    return  response()->json($rests);
    // CLEVER WAY WITHOUT LARAVEL METHODS 
    // -> BUT THEN LARAVEL PAGINATION FAILS
    // // initialized as empty arr
    // $restaurants = [];
    //  foreach ($Types_id_array as $id) {
    //     $type = Type::find($id);
    //     foreach ($type->restaurants as $restaurant) {
    //         array_push($restaurants, $restaurant);
    //     }    
    // }
    //img
    // if ($restaurant->img) {
    //     $restaurant->img = url('storage/' . $restaurant->img);
    // }
    }

    public function restMunu($id)
    {   
        $restaurants = Restaurant::query()->with('plates','types')->get();
        $restaurant = $restaurants->find($id);
     
        return response()->json($restaurant);
    }
}
