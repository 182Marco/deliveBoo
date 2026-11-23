@extends('layouts.app')

@section('title', __('partner.plates.new'))

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ url()->previous() }}"><x-icon name="arrow-left" size="18" /> {{ __('partner.common.back') }}</a>
    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.common.menu') }}</p>
            <h1 class="ui-title">{{ __('partner.plates.new') }}</h1>
            <p class="ui-muted">{!! __('partner.common.required', ['star' => '<span class="ui-required">*</span>']) !!}</p>
        </div>
    </div>

    <form class="adm-form ui-card ui-card--pad" action="{{ route('admin.plates.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        {{-- the restaurant of the previous page --}}
        <input type="hidden" name="restaurant_id" value="{{ $belonging_rest_id }}">

        <div class="adm-form-grid">
            <x-field name="name" :label="__('partner.common.name')" :value="old('name')" star />
            <x-field name="price" :label="__('partner.plates.priceEuro')" inputmode="decimal" :value="old('price')" star />
            <x-field name="description" :label="__('partner.common.description')" as="textarea" :value="old('description')" star wide />
            <x-field name="ingredients" :label="__('partner.common.ingredients')" :value="old('ingredients')" :hint="__('partner.plates.ingredientsHint')" star wide />
        </div>

        @include('admin.plates.partials.details', ['plate' => null])

        <div class="ui-field">
            <label class="ui-label" for="img">{{ __('partner.plates.photo') }}</label>
            <input class="ui-file" type="file" name="img" id="img" accept="image/*">
            @error('img')
                <p class="ui-field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <x-field name="img_url" :label="__('partner.plates.photoUrl')" type="url" placeholder="https://images.unsplash.com/photo-…" :value="old('img_url')" :hint="__('partner.plates.photoUrlHint')" />

        <div class="adm-form-foot">
            <button class="ui-btn ui-btn--primary" type="submit">{{ __('partner.plates.submitNew') }}</button>
            <a class="ui-btn ui-btn--ghost" href="{{ url()->previous() }}">{{ __('partner.common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
