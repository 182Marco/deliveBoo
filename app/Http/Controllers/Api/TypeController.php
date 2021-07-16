<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Type;

class typesController extends Controller
{
    public function index () {
        
        $types = Type::all()->toArray();

        // get an array of types with just name
        // $types_name = array_column($types, 'name');
        // $types_id = array_column($types, 'id');

        return response()->json( compact('types'));
    }
}
