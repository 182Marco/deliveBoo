<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Order;
use App\Mail\OrderedMessage;




class OrderController extends Controller
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

        $data = $r->all();

        // create instance
        $new_order = new Order();
        // FILLABLE
        $new_order->fill($data);

        // save in db
        $new_order->save();
        // relationship many to manysave
        // remember you can associate with syn only when new istance is already saved
        $new_order->plates()->sync($r->plates); 

        // MAIL
        Mail::to($r->customer_email)->send(new orderedmessage($data));
            
        return response()->json($data);
    }
}


        //Send email to admin
        //Mail::to('admin@site.it')->send(new ContactMessage($data));
