{{--
    Menu details of a dish, shared by create and edit. Everything here is optional:
    "Not declared" writes nothing, so an unknown answer never becomes a "no".
    $plate is null on create; $dietaryTags and $allergens come from the controller.
--}}
@php
    $months = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $dietNow = $plate?->dietaryTags->mapWithKeys(fn ($t) => [$t->code => $t->pivot->status])->all() ?? [];
    $allergenNow = $plate?->allergens->mapWithKeys(fn ($a) => [$a->code => $a->pivot->status])->all() ?? [];
@endphp

<div class="adm-form-grid">
    <div class="ui-field">
        <label class="ui-label" for="availability">Availability <span class="ui-required" aria-hidden="true">*</span></label>
        <select class="ui-input" name="availability" id="availability">
            @foreach (['available' => 'Available, guests can order it', 'sold_out' => 'Sold out for now', 'unavailable' => 'Not on the menu right now'] as $value => $text)
                <option value="{{ $value }}" @selected(old('availability', $plate?->availability ?? 'unavailable') === $value)>{{ $text }}</option>
            @endforeach
        </select>
        @error('availability')
            <p class="ui-field-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="ui-field">
        <label class="ui-label" for="course">Course</label>
        <select class="ui-input" name="course" id="course">
            <option value="">Not set</option>
            @foreach (\App\Plate::COURSES as $course)
                <option value="{{ $course }}" @selected(old('course', $plate?->course) === $course)>{{ ucfirst(str_replace('_', ' ', $course)) }}</option>
            @endforeach
        </select>
    </div>

    <x-field name="serves" label="Serves (people)" type="number" min="1" max="20" :value="old('serves', $plate?->serves ?? 1)" />
    <x-field name="calories" label="Calories (kcal per portion)" type="number" min="0" max="5000" :value="old('calories', $plate?->calories)" hint="Leave empty if you do not know." />

    <div class="ui-field">
        <label class="ui-label" for="available_from_month">Seasonal from</label>
        <select class="ui-input" name="available_from_month" id="available_from_month">
            <option value="">All year</option>
            @foreach ($months as $n => $month)
                <option value="{{ $n }}" @selected((int) old('available_from_month', $plate?->available_from_month) === $n)>{{ $month }}</option>
            @endforeach
        </select>
    </div>
    <div class="ui-field">
        <label class="ui-label" for="available_to_month">Seasonal until</label>
        <select class="ui-input" name="available_to_month" id="available_to_month">
            <option value="">All year</option>
            @foreach ($months as $n => $month)
                <option value="{{ $n }}" @selected((int) old('available_to_month', $plate?->available_to_month) === $n)>{{ $month }}</option>
            @endforeach
        </select>
        @error('available_to_month')
            <p class="ui-field-error" role="alert">{{ $message }}</p>
        @enderror
    </div>
</div>

<fieldset class="adm-fieldset">
    <legend class="adm-legend">Dietary information (optional)</legend>
    <p class="ui-hint">Only state what you have checked. "Not declared" leaves the information unknown to guests.</p>
    <div class="adm-form-grid">
        @foreach ($dietaryTags as $tag)
            <div class="ui-field">
                <label class="ui-label" for="diet_{{ $tag->code }}">{{ $tag->name }}</label>
                <select class="ui-input" name="diet[{{ $tag->code }}]" id="diet_{{ $tag->code }}">
                    @foreach (['' => 'Not declared', 'yes' => 'Yes', 'no' => 'No', 'unknown' => 'Not verified'] as $value => $text)
                        <option value="{{ $value }}" @selected(old("diet.{$tag->code}", $dietNow[$tag->code] ?? '') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</fieldset>

<fieldset class="adm-fieldset">
    <legend class="adm-legend">Allergens (optional)</legend>
    <p class="ui-hint">"Complete" means you checked all 14 allergens: the ones left "Not declared" are absent. Use "May contain" for cross-contact in your kitchen.</p>
    <div class="adm-form-grid">
        <div class="ui-field">
            <label class="ui-label" for="allergen_info">Allergen declaration</label>
            <select class="ui-input" name="allergen_info" id="allergen_info">
                @foreach (['' => 'Not declared', 'complete' => 'Complete', 'partial' => 'Partial'] as $value => $text)
                    <option value="{{ $value }}" @selected(old('allergen_info', $plate?->allergen_info ?? '') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        @foreach ($allergens as $allergen)
            <div class="ui-field">
                <label class="ui-label" for="allergen_{{ $allergen->code }}">{{ $allergen->name }}</label>
                <select class="ui-input" name="allergens[{{ $allergen->code }}]" id="allergen_{{ $allergen->code }}">
                    @foreach (['' => 'Not declared', 'contains' => 'Contains', 'may_contain' => 'May contain'] as $value => $text)
                        <option value="{{ $value }}" @selected(old("allergens.{$allergen->code}", $allergenNow[$allergen->code] ?? '') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</fieldset>
