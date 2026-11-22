@extends('layouts.app')

@section('title', $restaurant->name)

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.restaurants.index') }}"><x-icon name="arrow-left" size="18" /> Your restaurants</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Restaurant</p>
            <h1 class="ui-title">{{ $restaurant->name }}</h1>
        </div>
        <div class="adm-actions">
            <a class="ui-btn ui-btn--dark" href="{{ route('admin.plates.index', $restaurant->slug) }}"><x-icon name="utensils" size="18" /> Menu</a>
            <a class="ui-btn ui-btn--outline" href="{{ route('admin.orders.index', $restaurant->slug) }}"><x-icon name="receipt" size="18" /> Orders</a>
            <a class="ui-btn ui-btn--outline" href="{{ route('admin.restaurants.edit', $restaurant->id) }}"><x-icon name="edit" size="18" /> Edit</a>
        </div>
    </div>

    <div class="adm-detail">
        <x-photo class="adm-detail-photo" :src="$restaurant->img" :alt="$restaurant->name" />
        <dl class="adm-facts">
            @if (count($restaurant->types) > 0)
                <div class="adm-fact">
                    <dt class="adm-fact-label">Cuisines</dt>
                    <dd class="adm-fact-value adm-tags">
                        @foreach ($restaurant->types as $type)
                            <span class="ui-badge ui-badge--brand">{{ $type->name }}</span>
                        @endforeach
                    </dd>
                </div>
            @endif
            <div class="adm-fact">
                <dt class="adm-fact-label">Phone</dt>
                <dd class="adm-fact-value">{{ $restaurant->phone }}</dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">City</dt>
                <dd class="adm-fact-value">{{ $restaurant->city }}</dd>
            </div>
            <div class="adm-fact">
                <dt class="adm-fact-label">Address</dt>
                <dd class="adm-fact-value">{{ $restaurant->address }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
