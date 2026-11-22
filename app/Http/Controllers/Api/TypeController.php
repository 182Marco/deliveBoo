<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Type;
use Illuminate\Http\JsonResponse;

class TypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['types' => Type::all()]);
    }
}
