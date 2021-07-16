<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Type;

class TypeController extends Controller
{
    public function index () {
        $types = Type::all()->toArray();

        // get an array of types with just name
        // $types_name = array_column($types, 'name');
        // $types_id = array_column($types, 'id');

        return response()->json( compact('types'));
    }
}
