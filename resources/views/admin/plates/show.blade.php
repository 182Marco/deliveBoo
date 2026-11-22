@extends('layouts.app')

@section('title', $plate->name)

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.plates.index', $plate->restaurant->slug) }}"><x-icon name="arrow-left" size="18" /> Menu</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Dish</p>
            <h1 class="ui-title">{{ $plate->name }}</h1>
        </div>
        <div class="adm-actions">
            <a class="ui-btn ui-btn--outline" href="{{ route('admin.plates.edit', $plate->slug) }}"><x-icon name="edit" size="18" /> Edit</a>
            <form class="delete-post-form adm-inline-form" action="{{ route('admin.plate.delete', $plate->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="ui-btn ui-btn--danger" type="submit"><x-icon name="trash" size="18" /> Delete</button>
            </form>
        </div>
    </div>

    <div class="adm-detail">
        <x-photo class="adm-detail-photo" :src="$plate->img" :alt="$plate->name" />
        <dl class="adm-facts">
            <div class="adm-fact">
                <dt class="adm-fact-label">Price</dt>
                <dd class="adm-fact-value ui-price">€{{ number_format($plate->price, 2) }}</dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">Status</dt>
                <dd class="adm-fact-value">
                    @if ($plate->visible == 1)
                        <span class="ui-badge ui-badge--basil">Available</span>
                    @else
                        <span class="ui-badge ui-badge--tomato">Unavailable</span>
                    @endif
                </dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">Description</dt>
                <dd class="adm-fact-value">{{ $plate->description }}</dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">Ingredients</dt>
                <dd class="adm-fact-value">{{ $plate->ingredients }}</dd>
            </div>
        </dl>
    </div>
</div>
@include('layouts/deleteConfirm')
@endsection
