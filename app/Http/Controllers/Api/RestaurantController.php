<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Restaurant;
use App\Plate;
use App\Type;


class RestaurantController extends Controller
{
    public function restTopRated(){
        $allRests = Restaurant::all();

        $restsPlusAverageRateKey = [];

         //   VARIABLE USEFULL TO CHECK RESULTS
        // $ratesAverageCollection = [];

        foreach ($allRests as $rest ) {
          $RatesForEachObj =  $rest->rates()->select('rate')->get();

          //   transform obj in array _________________
          $ratesForEachAr = [];
          foreach ($RatesForEachObj as $key => $value) {
            array_push($ratesForEachAr,  $value->rate);
          }
          // _____________________________________________
       
          $average = array_sum($ratesForEachAr) / count($ratesForEachAr);

        //   VARIABLE USEFULL TO CHECK RESULTS
        //   array_push($ratesAverageCollection, $average); 
        $rest->average = $average;
        // $rest["average"] = $average;
  
        array_push($restsPlusAverageRateKey,  $rest );

        }
        
        //  ascendind sort restsPlusAverageRateKey by rate key
        usort($restsPlusAverageRateKey, fn($a, $b) => strcmp($a->average, $b->average));

        // descending sort restsPlusAverageRateKey by rate key
        $descRestsPlusAverageRateKey = array_reverse($restsPlusAverageRateKey);

        // getFirst twelve rates
        $topTwelve = array_slice($descRestsPlusAverageRateKey, 0, 12);


        return  response()->json($topTwelve);
    }




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

    public function restMunu($slug)
    {  
        $rest = Restaurant::where('slug', $slug)->first();
        $rest_id = $rest['id'];
        $rates = $rest->rates()->get();
        $types = $rest->types()->get();
        $typesNames = [];
        foreach ($types as $type) {
            array_push($typesNames,$type['name']);
        };
        $ratesNumbers = [];
        foreach ($rates as $rateAr) {
            array_push($ratesNumbers,$rateAr['rate']);
        };
        $average = array_sum($ratesNumbers) / count($ratesNumbers);
        $plates = Plate::where("restaurant_id", $rest_id)->paginate(4);
        $rest_type_menu = [
            "averageRate" => $average,
            "location" => $rest,
            "types" => $typesNames,
            "menu" => $plates,
        ];
   
        return response()->json($rest_type_menu);
    }
}
