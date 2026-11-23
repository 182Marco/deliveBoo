@extends('layouts.app')

@section('title', __('partner.common.yourRestaurants'))

@section('content')
<div class="ui-container">
    @if (session('deleted'))
        <div class="ui-notice ui-notice--success adm-flash" role="status">
            <x-icon name="check" /> <p>{!! __('partner.restaurants.deleted', ['name' => '<strong>' . e(session('deleted')) . '</strong>']) !!}</p>
        </div>
    @endif

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.restaurants.eyebrow') }}</p>
            <h1 class="ui-title">{{ __('partner.common.yourRestaurants') }}</h1>
            <p class="ui-muted">{{ __('partner.restaurants.lead') }}</p>
        </div>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.restaurants.create') }}">
            <x-icon name="plus" /> {{ __('partner.restaurants.new') }}
        </a>
    </div>

    @if ($auth_restaurants->isEmpty())
        <div class="ui-empty">
            <img class="ui-empty-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="">
            <p class="ui-subtitle">{{ __('partner.restaurants.emptyTitle') }}</p>
            <p class="ui-muted">{{ __('partner.restaurants.emptyText') }}</p>
        </div>
    @else
        <ul class="adm-grid">
            @foreach ($auth_restaurants as $auth_restaurant)
                <li class="adm-rest">
                    <x-photo class="adm-rest-photo" :src="$auth_restaurant->img" :alt="$auth_restaurant->name" />
                    <div class="adm-rest-body">
                        <h2 class="adm-rest-name">{{ str_replace('_', ' ', $auth_restaurant->name) }}</h2>
                        <p class="adm-meta"><x-icon name="pin" size="16" /> {{ $auth_restaurant->address }}, {{ $auth_restaurant->city }}</p>
                        <p class="adm-meta"><x-icon name="phone" size="16" /> {{ $auth_restaurant->phone }}</p>
                    </div>
                    <div class="adm-rest-foot">
                        <a class="ui-btn ui-btn--dark ui-btn--sm" href="{{ route('admin.plates.index', $auth_restaurant->slug) }}">
                            <x-icon name="utensils" size="16" /> {{ __('partner.common.menu') }}
                        </a>
                        <a class="ui-btn ui-btn--outline ui-btn--sm" href="{{ route('admin.orders.index', $auth_restaurant->slug) }}">
                            <x-icon name="receipt" size="16" /> {{ __('partner.common.orders') }}
                        </a>
                        <div class="adm-rest-tools">
                            <a class="ui-btn ui-btn--ghost ui-btn--icon ui-btn--icon-sm" href="{{ route('admin.restaurants.show', $auth_restaurant->id) }}" aria-label="{{ __('partner.common.viewItem', ['name' => $auth_restaurant->name]) }}" title="{{ __('partner.common.view') }}">
                                <x-icon name="eye" size="18" />
                            </a>
                            <a class="ui-btn ui-btn--ghost ui-btn--icon ui-btn--icon-sm" href="{{ route('admin.restaurants.edit', $auth_restaurant->id) }}" aria-label="{{ __('partner.common.editItem', ['name' => $auth_restaurant->name]) }}" title="{{ __('partner.common.edit') }}">
                                <x-icon name="edit" size="18" />
                            </a>
                            <form class="delete-post-form adm-inline-form" action="{{ route('admin.restaurants.destroy', $auth_restaurant->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="ui-btn ui-btn--ghost ui-btn--icon ui-btn--icon-sm" type="submit" aria-label="{{ __('partner.common.deleteItem', ['name' => $auth_restaurant->name]) }}" title="{{ __('partner.common.delete') }}">
                                    <x-icon name="trash" size="18" />
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    {{ $auth_restaurants->appends(request()->query())->links() }}
</div>
@include('layouts/logged')
@include('layouts/deleteConfirm')
@endsection
