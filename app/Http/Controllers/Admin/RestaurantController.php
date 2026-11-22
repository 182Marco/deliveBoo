<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Restaurant;
use App\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RestaurantController extends Controller
{
    // restaurants of the logged user (also the admin home)
    public function index()
    {
        $auth_restaurants = Restaurant::where('user_id', auth()->id())->paginate(5);

        return view('admin.restaurants.index', compact('auth_restaurants'));
    }

    public function create()
    {
        $types = Type::all();

        return view('admin.restaurants.create', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'unique:restaurants,name', 'max:80'],
            'phone' => ['required'],
            'city' => ['required'],
            'address' => ['required'],
            'types' => ['nullable', 'exists:types,id'],
            'img' => ['nullable', 'mimes:jpg,png,jpeg,webp'],
        ], [
            'required' => 'The :attribute is required!',
            'unique' => 'The :attribute is already used',
            'max' => 'Max :max characters allowed for the :attribute',
        ]);

        $data = $request->all();
        if ($request->hasFile('img')) {
            $data['img'] = $request->file('img')->store('restaurants-imgs');
        }

        $new_restaurant = new Restaurant($data);
        $new_restaurant->user_id = auth()->id();
        $new_restaurant->slug = Restaurant::uniqueSlug($new_restaurant->name);
        $new_restaurant->save();

        // types in the pivot table
        if ($request->has('types')) {
            $new_restaurant->types()->attach($data['types']);
        }

        return redirect()->route('admin.restaurants.show', $new_restaurant->id);
    }

    public function show($id)
    {
        $restaurant = Restaurant::findOrFail($id);

        if ($restaurant->user_id != auth()->id()) {
            return view('admin.notYours.restaurant');
        }

        return view('admin.restaurants.show', compact('restaurant'));
    }

    public function edit($id)
    {
        $restaurant = Restaurant::findOrFail($id);

        if ($restaurant->user_id != auth()->id()) {
            return view('admin.notYours.restaurant');
        }

        $types = Type::all();

        return view('admin.restaurants.edit', compact('restaurant', 'types'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', Rule::unique('restaurants')->ignore($id), 'max:80'],
            'phone' => ['required'],
            'city' => ['required'],
            'address' => ['required'],
            'types' => ['nullable', 'exists:types,id'],
            'img' => ['nullable', 'mimes:jpg,png,jpeg,webp'],
        ], [
            'required' => 'The :attribute is required!',
            'unique' => 'This :attribute is already used',
            'max' => 'you have exceeded the :max characters allowed for the :attribute',
        ]);

        $restaurant = Restaurant::findOrFail($id);
        $data = $request->all();

        // replace the image: delete the previous one
        if ($request->hasFile('img')) {
            if ($restaurant->img) {
                Storage::delete($restaurant->img);
            }
            $data['img'] = $request->file('img')->store('restaurants-imgs');
        }

        $restaurant->update($data);

        // no types in the form = remove all of them
        $restaurant->types()->sync($data['types'] ?? []);

        return redirect()->route('admin.restaurants.show', $restaurant->id);
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);

        if ($restaurant->img) {
            Storage::delete($restaurant->img);
        }

        // no orphans in the pivot table
        $restaurant->types()->detach();
        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('deleted', $restaurant->name);
    }
}
