<?php

namespace App\Http\Controllers\Admin;

use App\Allergen;
use App\Dietary\Derivation;
use App\DietaryTag;
use App\Http\Controllers\Controller;
use App\Plate;
use App\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PlateController extends Controller
{
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

        return view('admin.plates.create', [...compact('res_of_user', 'belonging_rest_id'), ...$this->formOptions()]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules(['restaurant_id' => ['required', 'integer']]));

        // SEC-1: the restaurant of the form must be one of the logged user's
        $restaurant = Restaurant::find($request->integer('restaurant_id'));
        if (! $restaurant || $request->user()->cannot('manage', $restaurant)) {
            return view('admin.notYours.plate');
        }

        // all(), not validated(): the other fields have no rule for every key
        $data = $this->plateData($request->except(['diet', 'allergens', 'img_url']));
        $data['img'] = $this->photo($request);

        $new_plate = new Plate($data);
        $new_plate->restaurant_id = $restaurant->id;
        $new_plate->slug = Plate::uniqueSlug($data['name'], $restaurant->slug);
        DB::transaction(function () use ($new_plate, $request) {
            $new_plate->save();
            $this->saveDetails($new_plate, $request);
        });

        return redirect()->route('admin.plates.show', $new_plate->slug);
    }

    public function show($slug)
    {
        $plate = Plate::where('slug', $slug)->firstOrFail();

        if ($plate->restaurant?->user_id != auth()->id()) {
            return view('admin.notYours.plate');
        }

        $plate->load('ingredientList', 'allergens', 'dietaryTags');

        return view('admin.plates.show', compact('plate'));
    }

    public function edit($slug)
    {
        $plate = Plate::where('slug', $slug)->firstOrFail();

        if ($plate->restaurant?->user_id != auth()->id()) {
            return view('admin.notYours.plate');
        }

        $plate->load('allergens', 'dietaryTags');

        return view('admin.plates.edit', ['plate' => $plate, ...$this->formOptions()]);
    }

    public function update(Request $request, $id)
    {
        $plate = Plate::findOrFail($id);
        // SEC-4: only the owner of the restaurant changes its plates
        if ($request->user()->cannot('manage', $plate)) {
            return view('admin.notYours.plate');
        }
        // the same dish name can exist in many restaurants, not twice in the same menu
        $request->validate($this->rules([
            'name' => ['required', Rule::unique('plates')->where('restaurant_id', $plate->restaurant_id)->whereNull('deleted_at')->ignore($id), 'max:80'],
        ]));

        $data = $this->plateData($request->except(['diet', 'allergens', 'restaurant_id', 'img_url']));

        // a new photo (upload or link) replaces the previous one; an uploaded file is deleted
        $photo = $this->photo($request);
        if ($photo !== null && $photo !== $plate->img) {
            if ($plate->img && ! self::isLink($plate->img)) {
                Storage::delete($plate->img);
            }
            $data['img'] = $photo;
        } else {
            unset($data['img']);
        }

        DB::transaction(function () use ($plate, $data, $request) {
            $plate->update($data);
            $this->saveDetails($plate, $request);
        });

        return redirect()->route('admin.plates.show', $plate->slug);
    }

    public function destroy($id)
    {
        $plate = Plate::findOrFail($id);
        // SEC-4
        if (auth()->user()->cannot('manage', $plate)) {
            return view('admin.notYours.plate');
        }
        $rest_slug = $plate->restaurant?->slug;

        // soft delete: the dish leaves the menu but stays in the history of its orders
        $plate->delete();

        return redirect()->route('admin.plates.index', [$rest_slug])->with('deleted', $plate->name);
    }

    // ---------------------------------------------------------------- shared by store and update

    private function rules(array $override = []): array
    {
        return [
            'name' => ['required', 'max:80'],
            'ingredients' => ['required', 'max:1000'],
            'description' => ['required'],
            // the old "visible" radio is still accepted (availability wins when both are sent)
            'availability' => ['required_without:visible', 'nullable', Rule::in(Plate::AVAILABILITIES)],
            'visible' => ['required_without:availability', 'nullable', 'boolean'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'img' => ['nullable', 'image'],
            // or the link of a photo hosted elsewhere: the site points to it, nothing is copied
            'img_url' => ['nullable', 'url:https', 'max:2048'],
            'course' => ['nullable', Rule::in(Plate::COURSES)],
            'serves' => ['nullable', 'integer', 'min:1', 'max:20'],
            'calories' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'available_from_month' => ['nullable', 'integer', 'between:1,12', 'required_with:available_to_month'],
            'available_to_month' => ['nullable', 'integer', 'between:1,12', 'required_with:available_from_month'],
            'allergen_info' => ['nullable', Rule::in([Plate::ALLERGENS_COMPLETE, Plate::ALLERGENS_PARTIAL])],
            'diet' => ['nullable', 'array'],
            'diet.*' => ['nullable', Rule::in(DietaryTag::STATUSES)],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['nullable', Rule::in([Allergen::CONTAINS, Allergen::MAY_CONTAIN])],
            ...$override,
        ];
    }

    // the photo of the form: an uploaded file wins over a link, null = none given
    private function photo(Request $request): ?string
    {
        if ($request->hasFile('img')) {
            return $request->file('img')->store('plates-imgs');
        }

        return $request->filled('img_url') ? trim($request->input('img_url')) : null;
    }

    private static function isLink(string $img): bool
    {
        return str_starts_with($img, 'http://') || str_starts_with($img, 'https://');
    }

    // an empty "serves" means one portion (the column is NOT NULL)
    private function plateData(array $data): array
    {
        if (array_key_exists('serves', $data) && $data['serves'] === null) {
            $data['serves'] = 1;
        }

        return $data;
    }

    private function formOptions(): array
    {
        return [
            'dietaryTags' => DietaryTag::orderBy('id')->get(),
            'allergens' => Allergen::orderBy('annex_number')->get(),
        ];
    }

    // ingredients, dietary and allergen declarations: "Not declared" (empty) writes no row
    private function saveDetails(Plate $plate, Request $request): void
    {
        $plate->syncIngredients(explode(',', (string) $request->input('ingredients')));

        // a declaration left as it was keeps its source; a changed one becomes the restaurant's
        $current = $plate->dietaryTags()->get()->mapWithKeys(fn ($t) => [$t->code => $t->pivot])->all();
        $tags = DietaryTag::pluck('id', 'code');
        $diet = collect($request->input('diet', []))->filter()->only($tags->keys())
            ->reject(fn ($status, $code) => ($current[$code]->source ?? null) === DietaryTag::FROM_INGREDIENTS && $current[$code]->status === $status)
            ->mapWithKeys(fn ($status, $code) => [$tags[$code] => ['status' => $status, 'source' => DietaryTag::FROM_RESTAURANT]]);
        $plate->dietaryTags()->sync($diet->all());

        $allergenIds = Allergen::pluck('id', 'code');
        $plate->allergens()->sync(collect($request->input('allergens', []))->filter()->only($allergenIds->keys())
            ->mapWithKeys(fn ($status, $code) => [$allergenIds[$code] => ['status' => $status]])->all());

        // the tags derived from the ingredients are recomputed from the new data
        Derivation::refresh($plate->fresh());
    }
}
