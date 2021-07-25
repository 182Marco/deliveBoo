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
    public function index(Request $r)
    {   
        // got the restaurant id from query ($r->fullUrl() ) 
        // dd($r);
        // than worked with string through php method to have just restaurant id
        $restaurant_id = chop(substr($r->fullUrl(), strpos($r->fullUrl(), "?") + 1), "=");  
        // substr($r->fullUrl(), strpos($r->fullUrl(), "?") + 1)  

        if(Restaurant::find($restaurant_id)){
            $restaurant = Restaurant::find($restaurant_id);
        } else {
            abort(404);
        }
  
        // User_id colum in the restaurant I've found must be the same id of who's logged
        if($restaurant['user_id'] == auth()->id()){
            // comparison of fy and restaurant id 
            $orders = Order::where('restaurant_id', $restaurant_id)->paginate(5); 
            return view('admin.orders.index', compact('orders','restaurant_id'));
        }
        // else we wouldn't show him competitors plates
            return "Here there are plates that do not belong to one of yours restaurants...
            we're sure it was just a mistake happened by accident :-) ";
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
        return 'this order doesn\'t belongs to one of your restaurants!';
    }
}