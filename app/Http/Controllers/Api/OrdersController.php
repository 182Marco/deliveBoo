<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\orderedMessage;
use Illuminate\Support\Facades\Mail;


class OrdersController extends Controller
{
    // save orders on db and notify with email
    public function store(Request $r){
        // VALIDATION
        $validator = Validator::make($r->all(), [
            'customer_name' => 'required',
            'customer_lastName' => 'required',
            'customer_email' => 'required',
            'customer_phone' => 'required',
            'customer_address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors() ]);
        }

        // create instance
        $new_order = new Order();
        // FILLABLE NOT WORKING
        // $new_order->fill($data);
        $new_order->restaurant_id = $r->restaurant_id;
        $new_order->price = $r->price;
        $new_order->customer_name = $r->customer_name;
        $new_order->customer_lastName = $r->customer_lastName;
        $new_order->customer_email = $r->customer_email;
        $new_order->customer_phone = $r->customer_phone;
        $new_order->customer_address = $r->customer_address;
        // save in db
        $new_order->save();
        // relationship many to manysave
        // remember you can associate with syn only when new istance is already saved
        $new_order->plates()->sync($r->plates); 

        // MAIL
        // Mail::to($r->customer_email)->send(new orderedMessage());
            
        return response()->json($new_order);
    }
}
