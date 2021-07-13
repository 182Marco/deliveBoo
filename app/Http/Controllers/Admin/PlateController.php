<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Plate;
use App\Restaurant;

class PlateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $r)
    {      
        // got the restaurant id from query ($r->fullUrl() ) 
        // than worked with string through php method to have just restaurant id 
        $restaurant_id = chop(substr( $r->fullUrl(), 35) ,"=");

        // getting all data of the restaurant in the query
        $restaurant = Restaurant::find($restaurant_id);


        if(Restaurant::find($restaurant_id)){
            $restaurant = Restaurant::find($restaurant_id);
        } else {
            abort(404);
        }

        // getting user_id associated with the restaurant above
        $user_id = $restaurant['user_id'];
  

        // now user_id found from query(above) must be the same of who's logged
        if($user_id == auth()->id()){
            // comparison of fy and restaurant id 
            $res_plates = Plate::where('restaurant_id', $restaurant_id)->get(); 
            return view('admin.plates.index', compact('res_plates','restaurant_id'));
        }
        // else we wouldn't show him competitors plates
            return "Here there are plates that do not belong to one of your restaurant...we're sure it was just a mistake happened by accident :-) ";
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {        
        //find plate in query
        $plate= Plate::find($id);



        // does the plate exist?
        if(! $plate) {
            abort(404);
        }else{
            // find restaurant to which plate in query belongs
            $rest_plate_belongs = Restaurant::find($plate['restaurant_id']);
            // find user_id to which restaurant above belongs 
            $user_id_plate_belongs =  $rest_plate_belongs['user_id'];

             // Does the authenticated user have the same Id of user 
             // which belongs restaurant which belongs the plate ?
            if($user_id_plate_belongs == auth()->id()){
                return view('admin.plates.show', compact('plate'));
            }
        }
        return 'this plate doesn\'t belongs to one of your restaurants!';
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $plate= Plate::find($id);
        $plate->delete();

        return redirect()->back()->with('deleted', $plate->name);
    }
}
