<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderedMessage;
use App\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // save the order, attach its plates and send the confirmation mail
    public function store(Request $r): JsonResponse
    {
        // manual Validator on purpose: Payment.vue expects 200 + {errors}, not a 422
        $validator = Validator::make($r->all(), [
            'customer_name' => 'required',
            'customer_lastName' => 'required',
            'customer_email' => 'required',
            'customer_phone' => 'required',
            'customer_address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $data = $r->all();

        // the pivot can be synced only once the order is saved
        Order::create($data)->plates()->sync($r->plates);

        Mail::to($r->customer_email)->send(new OrderedMessage($data));

        return response()->json($data);
    }
}
