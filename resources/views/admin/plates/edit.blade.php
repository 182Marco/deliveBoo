@extends('layouts.app')

@section('title', 'Edit ' . $plate->name)

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.plates.index', $plate->restaurant->slug) }}"><x-icon name="arrow-left" size="18" /> Menu</a>
    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Edit dish</p>
            <h1 class="ui-title">{{ $plate->name }}</h1>
            <p class="ui-muted">Fields marked with <span class="ui-required">*</span> are required.</p>
        </div>
        <form class="delete-post-form adm-inline-form" action="{{ route('admin.plate.delete', $plate->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button class="ui-btn ui-btn--danger" type="submit"><x-icon name="trash" size="18" /> Delete dish</button>
        </form>
    </div>

    <form class="adm-form ui-card ui-card--pad" action="{{ route('admin.plates.update', $plate->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="adm-form-grid">
            <x-field name="name" label="Name" :value="old('name', $plate->name)" star />
            <x-field name="price" label="Price (€)" inputmode="decimal" :value="old('price', $plate->price)" star />
            <x-field name="description" label="Description" as="textarea" :value="old('description', $plate->description)" star wide />
            <x-field name="ingredients" label="Ingredients" :value="old('ingredients', $plate->ingredients)" hint="Separate them with commas, main ones first." star wide />
        </div>

        @include('admin.plates.partials.details', ['plate' => $plate])

        <div class="ui-field">
            <label class="ui-label" for="img">Photo</label>
            @if ($plate->img)
                <x-photo class="adm-current-photo" :src="$plate->img" :alt="$plate->name" />
            @endif
            <input class="ui-file" type="file" name="img" id="img" accept="image/*">
            @error('img')
                <p class="ui-field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <x-field name="img_url" label="…or a link to a photo hosted online" type="url" placeholder="https://images.unsplash.com/photo-…" :value="old('img_url', \Illuminate\Support\Str::startsWith($plate->img, 'https://') ? $plate->img : null)" hint="An https link to an image you are allowed to use. We show it from its address, we never copy it. An uploaded file wins over the link." />

        <div class="adm-form-foot">
            <button class="ui-btn ui-btn--primary" type="submit">Save changes</button>
            <a class="ui-btn ui-btn--ghost" href="{{ route('admin.plates.index', $plate->restaurant->slug) }}">Cancel</a>
        </div>
    </form>
</div>
@include('layouts/deleteConfirm')
@endsection
