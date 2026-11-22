@extends('layouts.app')

@section('title', 'New dish')

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ url()->previous() }}"><x-icon name="arrow-left" size="18" /> Back</a>
    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Menu</p>
            <h1 class="ui-title">New dish</h1>
            <p class="ui-muted">Fields marked with <span class="ui-required">*</span> are required.</p>
        </div>
    </div>

    <form class="adm-form ui-card ui-card--pad" action="{{ route('admin.plates.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        {{-- the restaurant of the previous page --}}
        <input type="hidden" name="restaurant_id" value="{{ $belonging_rest_id }}">

        <div class="adm-form-grid">
            <x-field name="name" label="Name" :value="old('name')" star />
            <x-field name="price" label="Price (€)" inputmode="decimal" :value="old('price')" star />
            <x-field name="description" label="Description" as="textarea" :value="old('description')" star wide />
            <x-field name="ingredients" label="Ingredients" :value="old('ingredients')" hint="Separate them with commas." star wide />
        </div>

        <fieldset class="adm-fieldset">
            <legend class="adm-legend">Available now?</legend>
            <div class="adm-checks">
                <label class="ui-check" for="yes_visible">
                    <input class="ui-check-input" type="radio" name="visible" id="yes_visible" value="1" @checked(old('visible'))> Yes, guests can order it
                </label>
                <label class="ui-check" for="no_visible">
                    <input class="ui-check-input" type="radio" name="visible" id="no_visible" value="0" @checked(!old('visible'))> Not yet
                </label>
            </div>
        </fieldset>

        <div class="ui-field">
            <label class="ui-label" for="img">Photo</label>
            <input class="ui-file" type="file" name="img" id="img" accept="image/*">
            @error('img')
                <p class="ui-field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="adm-form-foot">
            <button class="ui-btn ui-btn--primary" type="submit">Add to menu</button>
            <a class="ui-btn ui-btn--ghost" href="{{ url()->previous() }}">Cancel</a>
        </div>
    </form>
</div>
@endsection
