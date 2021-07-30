<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Order;
use App\Restaurant;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug)
    {   
        $belonging_rest = Restaurant::where('slug', $slug)->get();
        $belonging_rest = $belonging_rest[0];
        // substr($r->fullUrl(), strpos($r->fullUrl(), "?") + 1)  

        if(!$belonging_rest){
            abort(404);
        } 
         
        // User_id colum in the restaurant I've found must be the same id of who's logged
        if($belonging_rest['user_id'] == auth()->id()){
            // comparison of fy and restaurant id 
            $orders = Order::where('restaurant_id', $belonging_rest['id'])->orderBy('created_at', 'DESC')->paginate(5);
            $restaurant_id = $belonging_rest['id'];

            return view('admin.orders.index', compact('orders','restaurant_id'));
        }
        // else we wouldn't show him competitors plates
            return view ('admin.notYours.orders');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {        
        $rough_data = Order::with('plates',
                        'restaurant',)
                        ->where('id',$id)
                        ->get();

        $data=$rough_data[0];

        
        //find order in query
        $order = Order::find($id); 
    
        // // does the order exist?
        if(!$order) {
            abort(404);
        }else{
            
            $rest_order_belongs = Restaurant::find($order['restaurant_id']);
            
            $user_id_order_belongs =  $rest_order_belongs['user_id'];         

            if($user_id_order_belongs == auth()->id()){
                return view('admin.orders.show', compact('data'));
            }
        }
        return view ('admin.notYours.plate');
    }
}