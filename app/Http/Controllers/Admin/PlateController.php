<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Plate;
use App\Restaurant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; //validation
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class PlateController extends Controller
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

        if(!$belonging_rest){
            abort(404);
        } 
  
        // User_id colum in the restaurant I've found must be the same id of who's logged
        if($belonging_rest['user_id'] == auth()->id()){
            $id = $belonging_rest['id'];
            // comparison of fy and restaurant id 
            $res_plates = Plate::where('restaurant_id', $id)->paginate(4);
            return view('admin.plates.index', compact('res_plates','slug'));
        }
        // else we wouldn't show him competitors plates
        return view ('admin.notYours.plate');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($slug)
    {

        $belonging_rest = Restaurant::where('slug', $slug)->get();
        $belonging_rest_id = $belonging_rest[0]['id'];
        // get restaurant from which user arrives 
        // ->request->fullUrl + with strings to get query
        // $prev_view_res_id = chop(substr($r->fullUrl(), strpos($r->fullUrl(), "?") + 1), "=");
        //  get the restaurants associated with the logged user
        $res_of_user = Restaurant::where('user_id', Auth::id())->get();

        return view ('admin.plates.create', compact('res_of_user', 'belonging_rest_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {     
        //validation
        $request->validate([
            'name'=> 'required | max: 50',
            'ingredients' => 'required',
            'description'=> 'required',
            'visible'=> 'required',
            'price'=> 'required',
            'img'=> 'nullable | image'
        ],        
        [
            'required'=> 'Required Field!',
            'max' => 'you have exceeded the :max characters allowed for the :attribute'
            ]);

            $data=$request->all();

            //Add Cover Image ( if exists)
            if(array_key_exists('img', $data)) {
               $img_path=Storage::put('plates-imgs', $data['img']);
   
               //overwrite img with file path
               $data['img']=$img_path; //Url
           }
   
           //create e save records on db
           $new_plate = new Plate();
           
            // create slug from name asigned by user
            $new_plate->slug = Str::slug( $data['name'], '-');
           //fillable in plate
           $new_plate->fill($data);
           
           //save
           $new_plate->save();
   
           return redirect()->route('admin.plates.show', $new_plate->slug);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {        
        //find plate in query
        $plate = Plate::where('slug', $slug)->get();
        $plate = $plate[0];

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
        return view ('admin.notYours.plate');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        //find plate in query
        $plate = Plate::where('slug', $slug)->get();
        $plate = $plate[0];

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
                return view('admin.plates.edit', compact('plate'));
            }
        }
        return view ('admin.notYours.plate');
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
        //validation
        $request->validate([
            'name'=> [
                'required',
                 Rule::unique('plates')->ignore($id),
                'max:80'
            ],
            
            'ingredients'=> 'required',
            'description'=> 'required',
            'visible'=> 'required',
            'price'=> 'required',
            'img'=> 'nullable |image',
        ],
        
        [
            'required'=> 'Required Field!',
            'unique'=> 'This :attribute is already used',
            'max' => 'you have exceeded the :max characters allowed for the :attribute'
            ]);
            
            $data= $request->all();

        $plate = Plate::find($id);

        //Image update
        if (array_key_exists('img', $data)) {
            //delete previous
            if ($plate->img) {
                Storage::delete($plate->img);
            }
            //set new one
            $data['img']= Storage::put('plates-imgs', $data['img']);
        }

        $plate->update($data); //fillable

        return redirect()->route('admin.plates.show', $plate->slug);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $plate = Plate::find($id);
        $restaurant_id = $plate['restaurant_id'];
        $restaurant = Restaurant::find($restaurant_id);
        $rest_slug = $restaurant['slug'];
        $plate->delete();
        // do not leave orphans in pivot table if element cancelled
        $plate->orders()->detach();

        return redirect()->route('admin.plates.index', [$rest_slug])->with('deleted', $plate->name); 
    }
}
