{{--
    Menu details of a dish, shared by create and edit. Everything here is optional:
    "Not declared" writes nothing, so an unknown answer never becomes a "no".
    $plate is null on create; $dietaryTags and $allergens come from the controller.
--}}
@php
    // month names in the current language (Carbon follows app()->getLocale())
    $months = collect(range(1, 12))->mapWithKeys(fn ($n) => [$n => ucfirst(\Illuminate\Support\Carbon::create(2000, $n, 1)->translatedFormat('F'))])->all();
    $dietNow = $plate?->dietaryTags->mapWithKeys(fn ($t) => [$t->code => $t->pivot->status])->all() ?? [];
    $allergenNow = $plate?->allergens->mapWithKeys(fn ($a) => [$a->code => $a->pivot->status])->all() ?? [];
@endphp

<div class="adm-form-grid">
    <div class="ui-field">
        <label class="ui-label" for="availability">{{ __('partner.plates.availability') }} <span class="ui-required" aria-hidden="true">*</span></label>
        <select class="ui-input" name="availability" id="availability">
            @foreach (['available', 'sold_out', 'unavailable'] as $value)
                <option value="{{ $value }}" @selected(old('availability', $plate?->availability ?? 'unavailable') === $value)>{{ __('partner.plates.availabilityOptions.' . $value) }}</option>
            @endforeach
        </select>
        @error('availability')
            <p class="ui-field-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="ui-field">
        <label class="ui-label" for="course">{{ __('partner.plates.course') }}</label>
        <select class="ui-input" name="course" id="course">
            <option value="">{{ __('partner.common.notSet') }}</option>
            @foreach (\App\Plate::COURSES as $course)
                <option value="{{ $course }}" @selected(old('course', $plate?->course) === $course)>{{ __('partner.plates.courses.' . $course) }}</option>
            @endforeach
        </select>
    </div>

    <x-field name="serves" :label="__('partner.plates.serves')" type="number" min="1" max="20" :value="old('serves', $plate?->serves ?? 1)" />
    <x-field name="calories" :label="__('partner.plates.calories')" type="number" min="0" max="5000" :value="old('calories', $plate?->calories)" :hint="__('partner.plates.caloriesHint')" />

    <div class="ui-field">
        <label class="ui-label" for="available_from_month">{{ __('partner.plates.seasonalFrom') }}</label>
        <select class="ui-input" name="available_from_month" id="available_from_month">
            <option value="">{{ __('partner.plates.allYear') }}</option>
            @foreach ($months as $n => $month)
                <option value="{{ $n }}" @selected((int) old('available_from_month', $plate?->available_from_month) === $n)>{{ $month }}</option>
            @endforeach
        </select>
    </div>
    <div class="ui-field">
        <label class="ui-label" for="available_to_month">{{ __('partner.plates.seasonalUntil') }}</label>
        <select class="ui-input" name="available_to_month" id="available_to_month">
            <option value="">{{ __('partner.plates.allYear') }}</option>
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
    <legend class="adm-legend">{{ __('partner.plates.dietTitle') }}</legend>
    <p class="ui-hint">{{ __('partner.plates.dietHint') }}</p>
    <div class="adm-form-grid">
        @foreach ($dietaryTags as $tag)
            <div class="ui-field">
                <label class="ui-label" for="diet_{{ $tag->code }}">{{ $tag->name }}</label>
                <select class="ui-input" name="diet[{{ $tag->code }}]" id="diet_{{ $tag->code }}">
                    @foreach (['' => 'notDeclared', 'yes' => 'yes', 'no' => 'no', 'unknown' => 'unknown'] as $value => $label)
                        <option value="{{ $value }}" @selected(old("diet.{$tag->code}", $dietNow[$tag->code] ?? '') === $value)>{{ __('partner.plates.dietStatus.' . $label) }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</fieldset>

<fieldset class="adm-fieldset">
    <legend class="adm-legend">{{ __('partner.plates.allergensTitle') }}</legend>
    <p class="ui-hint">{{ __('partner.plates.allergensHint') }}</p>
    <div class="adm-form-grid">
        <div class="ui-field">
            <label class="ui-label" for="allergen_info">{{ __('partner.plates.allergenDeclaration') }}</label>
            <select class="ui-input" name="allergen_info" id="allergen_info">
                @foreach (['' => 'notDeclared', 'complete' => 'complete', 'partial' => 'partial'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('allergen_info', $plate?->allergen_info ?? '') === $value)>{{ __('partner.plates.declarationOptions.' . $label) }}</option>
                @endforeach
            </select>
        </div>
        @foreach ($allergens as $allergen)
            <div class="ui-field">
                <label class="ui-label" for="allergen_{{ $allergen->code }}">{{ $allergen->name }}</label>
                <select class="ui-input" name="allergens[{{ $allergen->code }}]" id="allergen_{{ $allergen->code }}">
                    @foreach (['' => 'notDeclared', 'contains' => 'contains', 'may_contain' => 'may_contain'] as $value => $label)
                        <option value="{{ $value }}" @selected(old("allergens.{$allergen->code}", $allergenNow[$allergen->code] ?? '') === $value)>{{ __('partner.plates.allergenStatus.' . $label) }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</fieldset>
