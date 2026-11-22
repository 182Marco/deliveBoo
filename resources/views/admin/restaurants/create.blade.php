@extends('layouts.app')

@section('title', 'New restaurant')

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.restaurants.index') }}"><x-icon name="arrow-left" size="18" /> Your restaurants</a>
    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Partner hub</p>
            <h1 class="ui-title">New restaurant</h1>
            <p class="ui-muted">Fields marked with <span class="ui-required">*</span> are required.</p>
        </div>
    </div>

    <form class="adm-form ui-card ui-card--pad" action="{{ route('admin.restaurants.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="adm-form-grid">
            <x-field name="name" label="Name" :value="old('name')" star wide />
            <x-field name="phone" label="Phone" type="tel" :value="old('phone')" star />
            <x-field name="city" label="City" :value="old('city')" star />
            <x-field name="address" label="Address" :value="old('address')" star wide />
            <x-field name="description" label="Description" as="textarea" :value="old('description')" hint="What guests should know about your kitchen (optional)." wide />
            <div class="ui-field">
                <label class="ui-label" for="price_range">Price range</label>
                <select class="ui-input" name="price_range" id="price_range">
                    <option value="">Not set</option>
                    @foreach ([1 => '€ · budget', 2 => '€€ · mid-range', 3 => '€€€ · premium', 4 => '€€€€ · fine dining'] as $value => $text)
                        <option value="{{ $value }}" @selected((int) old('price_range') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <fieldset class="adm-fieldset">
            <legend class="adm-legend">Cuisines</legend>
            <div class="adm-checks">
                @foreach ($types as $type)
                    <label class="ui-check" for="type{{ $loop->iteration }}">
                        <input class="ui-check-input" type="checkbox" name="types[]" id="type{{ $loop->iteration }}" value="{{ $type->id }}" @checked(in_array($type->id, old('types', [])))>
                        {{ $type->name }}
                    </label>
                @endforeach
            </div>
            @error('types')
                <p class="ui-field-error" role="alert">{{ $message }}</p>
            @enderror
        </fieldset>

        <div class="ui-field">
            <label class="ui-label" for="img">Cover photo</label>
            <input class="ui-file" type="file" name="img" id="img" accept="image/*">
            @error('img')
                <p class="ui-field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="adm-form-foot">
            <button class="ui-btn ui-btn--primary" type="submit">Create restaurant</button>
            <a class="ui-btn ui-btn--ghost" href="{{ route('admin.restaurants.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
