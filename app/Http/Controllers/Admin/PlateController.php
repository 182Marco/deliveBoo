<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Plate;
use App\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlateController extends Controller
{
    private const MESSAGES = [
        'required' => 'Required Field!',
        'unique' => 'This :attribute is already used',
        'max' => 'you have exceeded the :max characters allowed for the :attribute',
    ];

    // plates of a restaurant (by slug), only for its owner
    public function index($slug)
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        if ($restaurant->user_id != auth()->id()) {
            return view('admin.notYours.plate');
        }

        $res_plates = $restaurant->plates()->paginate(4);

        return view('admin.plates.index', compact('res_plates', 'slug'));
    }

    public function create($slug)
    {
        $belonging_rest_id = Restaurant::where('slug', $slug)->firstOrFail()->id;
        // the select of the form lists only the restaurants of the logged user
        $res_of_user = Restaurant::where('user_id', Auth::id())->get();

        return view('admin.plates.create', compact('res_of_user', 'belonging_rest_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:50'],
            'ingredients' => ['required'],
            'description' => ['required'],
            'visible' => ['required'],
            'price' => ['required'],
            'img' => ['nullable', 'image'],
        ], self::MESSAGES);

        // all(), not validated(): restaurant_id comes from the form without a rule
        $data = $request->all();
        if ($request->hasFile('img')) {
            $data['img'] = $request->file('img')->store('plates-imgs');
        }

        $new_plate = new Plate($data);
        $new_plate->slug = Str::slug($data['name'], '-');
        $new_plate->save();

        return redirect()->route('admin.plates.show', $new_plate->slug);
    }

    public function show($slug)
    {
        $plate = Plate::where('slug', $slug)->firstOrFail();

        if ($plate->restaurant?->user_id != auth()->id()) {
            return view('admin.notYours.plate');
        }

        return view('admin.plates.show', compact('plate'));
    }

    public function edit($slug)
    {
        $plate = Plate::where('slug', $slug)->firstOrFail();

        if ($plate->restaurant?->user_id != auth()->id()) {
            return view('admin.notYours.plate');
        }

        return view('admin.plates.edit', compact('plate'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', Rule::unique('plates')->ignore($id), 'max:80'],
            'ingredients' => ['required'],
            'description' => ['required'],
            'visible' => ['required'],
            'price' => ['required'],
            'img' => ['nullable', 'image'],
        ], self::MESSAGES);

        $plate = Plate::findOrFail($id);
        $data = $request->all();

        // replace the image: delete the previous one
        if ($request->hasFile('img')) {
            if ($plate->img) {
                Storage::delete($plate->img);
            }
            $data['img'] = $request->file('img')->store('plates-imgs');
        }

        $plate->update($data);

        return redirect()->route('admin.plates.show', $plate->slug);
    }

    public function destroy($id)
    {
        $plate = Plate::findOrFail($id);
        $rest_slug = $plate->restaurant?->slug;

        $plate->delete();
        // do not leave orphans in the pivot table
        $plate->orders()->detach();

        return redirect()->route('admin.plates.index', [$rest_slug])->with('deleted', $plate->name);
    }
}
