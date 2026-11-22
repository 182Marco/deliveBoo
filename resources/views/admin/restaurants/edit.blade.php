@extends('layouts.app')

@section('title', 'Edit ' . $restaurant->name)

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.restaurants.index') }}"><x-icon name="arrow-left" size="18" /> Your restaurants</a>
    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Edit restaurant</p>
            <h1 class="ui-title">{{ $restaurant->name }}</h1>
            <p class="ui-muted">Fields marked with <span class="ui-required">*</span> are required.</p>
        </div>
    </div>

    <form class="adm-form ui-card ui-card--pad" action="{{ route('admin.restaurants.update', $restaurant->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="adm-form-grid">
            <x-field name="name" label="Name" :value="old('name', $restaurant->name)" star wide />
            <x-field name="phone" label="Phone" type="tel" :value="old('phone', $restaurant->phone)" star />
            <x-field name="city" label="City" :value="old('city', $restaurant->city)" star />
            <x-field name="address" label="Address" :value="old('address', $restaurant->address)" star wide />
        </div>

        <fieldset class="adm-fieldset">
            <legend class="adm-legend">Cuisines</legend>
            <div class="adm-checks">
                @foreach ($types as $type)
                    <label class="ui-check" for="type{{ $loop->iteration }}">
                        <input class="ui-check-input" type="checkbox" name="types[]" id="type{{ $loop->iteration }}" value="{{ $type->id }}"
                            @checked($errors->any() ? in_array($type->id, old('types', [])) : $restaurant->types->contains($type->id))>
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
            @if ($restaurant->img)
                <x-photo class="adm-current-photo" :src="$restaurant->img" :alt="$restaurant->name" />
            @endif
            <input class="ui-file" type="file" name="img" id="img" accept="image/*">
            @error('img')
                <p class="ui-field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="adm-form-foot">
            <button class="ui-btn ui-btn--primary" type="submit">Save changes</button>
            <a class="ui-btn ui-btn--ghost" href="{{ route('admin.restaurants.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
