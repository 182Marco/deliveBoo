@extends('layouts.app')

@section('title', __('partner.restaurants.new'))

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.restaurants.index') }}"><x-icon name="arrow-left" size="18" /> {{ __('partner.common.yourRestaurants') }}</a>
    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.restaurants.eyebrow') }}</p>
            <h1 class="ui-title">{{ __('partner.restaurants.new') }}</h1>
            <p class="ui-muted">{!! __('partner.common.required', ['star' => '<span class="ui-required">*</span>']) !!}</p>
        </div>
    </div>

    <form class="adm-form ui-card ui-card--pad" action="{{ route('admin.restaurants.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="adm-form-grid">
            <x-field name="name" :label="__('partner.common.name')" :value="old('name')" star wide />
            <x-field name="phone" :label="__('partner.common.phone')" type="tel" :value="old('phone')" star />
            <x-field name="city" :label="__('partner.common.city')" :value="old('city')" star />
            <x-field name="address" :label="__('partner.common.address')" :value="old('address')" star wide />
            <x-field name="description" :label="__('partner.common.description')" as="textarea" :value="old('description')" :hint="__('partner.restaurants.descriptionHint')" wide />
            <div class="ui-field">
                <label class="ui-label" for="price_range">{{ __('partner.restaurants.priceRange') }}</label>
                <select class="ui-input" name="price_range" id="price_range">
                    <option value="">{{ __('partner.common.notSet') }}</option>
                    @foreach ([1, 2, 3, 4] as $value)
                        <option value="{{ $value }}" @selected((int) old('price_range') === $value)>{{ __('partner.restaurants.priceRanges.' . $value) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <fieldset class="adm-fieldset">
            <legend class="adm-legend">{{ __('partner.common.cuisines') }}</legend>
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
            <label class="ui-label" for="img">{{ __('partner.restaurants.coverPhoto') }}</label>
            <input class="ui-file" type="file" name="img" id="img" accept="image/*">
            @error('img')
                <p class="ui-field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="adm-form-foot">
            <button class="ui-btn ui-btn--primary" type="submit">{{ __('partner.restaurants.submitNew') }}</button>
            <a class="ui-btn ui-btn--ghost" href="{{ route('admin.restaurants.index') }}">{{ __('partner.common.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
