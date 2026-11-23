@extends('layouts.app')

@section('title', $plate->name)

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.plates.index', $plate->restaurant->slug) }}"><x-icon name="arrow-left" size="18" /> {{ __('partner.common.menu') }}</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.plates.eyebrow') }}</p>
            <h1 class="ui-title">{{ $plate->name }}</h1>
        </div>
        <div class="adm-actions">
            <a class="ui-btn ui-btn--outline" href="{{ route('admin.plates.edit', $plate->slug) }}"><x-icon name="edit" size="18" /> {{ __('partner.common.edit') }}</a>
            <form class="delete-post-form adm-inline-form" action="{{ route('admin.plate.delete', $plate->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="ui-btn ui-btn--danger" type="submit"><x-icon name="trash" size="18" /> {{ __('partner.common.delete') }}</button>
            </form>
        </div>
    </div>

    <div class="adm-detail">
        <x-photo class="adm-detail-photo" :src="$plate->img" :alt="$plate->name" />
        <dl class="adm-facts">
            <div class="adm-fact">
                <dt class="adm-fact-label">{{ __('partner.common.price') }}</dt>
                <dd class="adm-fact-value ui-price">€{{ number_format($plate->price, 2) }}</dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">{{ __('partner.common.status') }}</dt>
                <dd class="adm-fact-value">
                    @if ($plate->availability === 'available')
                        <span class="ui-badge ui-badge--basil">{{ __('partner.common.available') }}</span>
                    @elseif ($plate->availability === 'sold_out')
                        <span class="ui-badge ui-badge--tomato">{{ __('partner.common.soldOut') }}</span>
                    @else
                        <span class="ui-badge ui-badge--tomato">{{ __('partner.common.unavailable') }}</span>
                    @endif
                    @if ($plate->available_from_month && $plate->available_to_month)
                        <span class="ui-muted">{{ __('partner.plates.seasonal', ['from' => ucfirst(\Illuminate\Support\Carbon::create(2000, $plate->available_from_month, 1)->translatedFormat('F')), 'to' => ucfirst(\Illuminate\Support\Carbon::create(2000, $plate->available_to_month, 1)->translatedFormat('F'))]) }}</span>
                    @endif
                </dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">{{ __('partner.common.description') }}</dt>
                <dd class="adm-fact-value">{{ $plate->description }}</dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">{{ __('partner.common.ingredients') }}</dt>
                <dd class="adm-fact-value">{{ $plate->ingredientList->pluck('name')->implode(', ') ?: $plate->ingredients }}</dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">{{ __('partner.plates.allergens') }}</dt>
                <dd class="adm-fact-value">
                    @if ($plate->allergen_info)
                        {{ __('partner.plates.declaration.' . $plate->allergen_info) }}{{ $plate->allergens->isNotEmpty() ? ': ' . $plate->allergens->map(fn ($a) => $a->name . ($a->pivot->status === 'may_contain' ? ' (' . __('partner.plates.mayContain') . ')' : ''))->implode(', ') : ($plate->allergen_info === 'complete' ? ', ' . __('partner.plates.nonePresent') : '') }}
                    @else
                        {{ __('partner.plates.notDeclared') }}
                    @endif
                </dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">{{ __('partner.plates.dietary') }}</dt>
                <dd class="adm-fact-value">
                    {{ $plate->dietaryTags->map(fn ($t) => $t->name . ': ' . __('partner.plates.dietValue.' . $t->pivot->status) . ($t->pivot->source === 'ingredients' ? ' (' . __('partner.plates.fromIngredients') . ')' : ''))->implode(' · ') ?: __('partner.plates.notDeclared') }}
                </dd>
            </div>
        </dl>
    </div>
</div>
@include('layouts/deleteConfirm')
@endsection
