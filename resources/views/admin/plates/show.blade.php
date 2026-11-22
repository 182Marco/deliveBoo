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
                    @if ($plate->availability === 'available')
                        <span class="ui-badge ui-badge--basil">Available</span>
                    @elseif ($plate->availability === 'sold_out')
                        <span class="ui-badge ui-badge--tomato">Sold out</span>
                    @else
                        <span class="ui-badge ui-badge--tomato">Unavailable</span>
                    @endif
                    @if ($plate->available_from_month && $plate->available_to_month)
                        <span class="ui-muted">Seasonal: {{ date('F', mktime(0, 0, 0, $plate->available_from_month, 1)) }}–{{ date('F', mktime(0, 0, 0, $plate->available_to_month, 1)) }}</span>
                    @endif
                </dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">Description</dt>
                <dd class="adm-fact-value">{{ $plate->description }}</dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">Ingredients</dt>
                <dd class="adm-fact-value">{{ $plate->ingredientList->pluck('name')->implode(', ') ?: $plate->ingredients }}</dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">Allergens</dt>
                <dd class="adm-fact-value">
                    @if ($plate->allergen_info)
                        {{ ucfirst($plate->allergen_info) }} declaration{{ $plate->allergens->isNotEmpty() ? ': ' . $plate->allergens->map(fn ($a) => $a->name . ($a->pivot->status === 'may_contain' ? ' (may contain)' : ''))->implode(', ') : ($plate->allergen_info === 'complete' ? ', none present' : '') }}
                    @else
                        Not declared
                    @endif
                </dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">Dietary</dt>
                <dd class="adm-fact-value">
                    {{ $plate->dietaryTags->map(fn ($t) => $t->name . ': ' . ['yes' => 'yes', 'no' => 'no', 'unknown' => 'not verified'][$t->pivot->status] . ($t->pivot->source === 'ingredients' ? ' (from ingredients)' : ''))->implode(' · ') ?: 'Not declared' }}
                </dd>
            </div>
        </dl>
    </div>
</div>
@include('layouts/deleteConfirm')
@endsection
