@extends('layouts.app')

@section('title', __('partner.meta.hub'))

{{-- not routed: the partner home is RestaurantController@index --}}
@section('content')
<div class="ui-container ui-container--narrow">
    @if (session('status'))
        <div class="ui-notice ui-notice--success adm-flash" role="status">{{ session('status') }}</div>
    @endif
    <div class="ui-empty">
        <p class="ui-subtitle">{{ __('partner.home.loggedIn') }}</p>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.restaurants.index') }}">{{ __('partner.header.myRestaurants') }}</a>
    </div>
</div>
@endsection
