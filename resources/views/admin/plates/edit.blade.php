@extends('layouts.app')

@section('title', __('partner.common.editItem', ['name' => $plate->name]))

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.plates.index', $plate->restaurant->slug) }}"><x-icon name="arrow-left" size="18" /> {{ __('partner.common.menu') }}</a>
    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.plates.editEyebrow') }}</p>
            <h1 class="ui-title">{{ $plate->name }}</h1>
            <p class="ui-muted">{!! __('partner.common.required', ['star' => '<span class="ui-required">*</span>']) !!}</p>
        </div>
        <form class="delete-post-form adm-inline-form" action="{{ route('admin.plate.delete', $plate->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button class="ui-btn ui-btn--danger" type="submit"><x-icon name="trash" size="18" /> {{ __('partner.plates.deleteDish') }}</button>
        </form>
    </div>

    <form class="adm-form ui-card ui-card--pad" action="{{ route('admin.plates.update', $plate->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="adm-form-grid">
            <x-field name="name" :label="__('partner.common.name')" :value="old('name', $plate->name)" star />
            <x-field name="price" :label="__('partner.plates.priceEuro')" inputmode="decimal" :value="old('price', $plate->price)" star />
            <x-field name="description" :label="__('partner.common.description')" as="textarea" :value="old('description', $plate->description)" star wide />
            <x-field name="ingredients" :label="__('partner.common.ingredients')" :value="old('ingredients', $plate->ingredients)" :hint="__('partner.plates.ingredientsHint')" star wide />
        </div>

        @include('admin.plates.partials.details', ['plate' => $plate])

        <div class="ui-field">
            <label class="ui-label" for="img">{{ __('partner.plates.photo') }}</label>
            @if ($plate->img)
                <x-photo class="adm-current-photo" :src="$plate->img" :alt="$plate->name" />
            @endif
            <input class="ui-file" type="file" name="img" id="img" accept="image/*">
            @error('img')
                <p class="ui-field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <x-field name="img_url" :label="__('partner.plates.photoUrl')" type="url" placeholder="https://images.unsplash.com/photo-…" :value="old('img_url', \Illuminate\Support\Str::startsWith($plate->img, 'https://') ? $plate->img : null)" :hint="__('partner.plates.photoUrlHint')" />

        <div class="adm-form-foot">
            <button class="ui-btn ui-btn--primary" type="submit">{{ __('partner.common.save') }}</button>
            <a class="ui-btn ui-btn--ghost" href="{{ route('admin.plates.index', $plate->restaurant->slug) }}">{{ __('partner.common.cancel') }}</a>
        </div>
    </form>
</div>
@include('layouts/deleteConfirm')
@endsection
