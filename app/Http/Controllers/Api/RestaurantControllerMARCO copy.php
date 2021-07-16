<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Restaurant;
use App\Type;
use Illuminate\Support\Facades\DB;

class RestaurantController extends Controller
{
    // public function index($id){

    // // $query = $r->getUri();

    // // return Restaurant::find($id);
    // return ($id);

    // // $restaurants = Restaurant::whereHas('types', function($query) {
    // //     $query->where('id', 3);
    // // })
    // // ->get();

    // //     return response()->json(compact('restaurants'));
    // // ->with('criterias', 'criterias.alert')->get()
    // }

    public function index($ids){
    
    $Types_id_array = explode(",",$ids);
    
    $restaurants = [];

     foreach ($Types_id_array as $id) {
        $type = Type::find($id);
        foreach ($type->restaurants as $restaurant) {
            array_push($restaurants, $restaurant);
        }
    }


    // foreach ($Types_array as $type) {
    //     array_push($restaurants, Restaurant::find($type));
    // }

    // return Restaurant::find($id);
    return  response()->json($restaurants);
    }
}