@extends('layouts.app')

@section('title', 'Partner hub')

{{-- not routed: the partner home is RestaurantController@index --}}
@section('content')
<div class="ui-container ui-container--narrow">
    @if (session('status'))
        <div class="ui-notice ui-notice--success adm-flash" role="status">{{ session('status') }}</div>
    @endif
    <div class="ui-empty">
        <p class="ui-subtitle">You are logged in</p>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.restaurants.index') }}">My restaurants</a>
    </div>
</div>
@endsection
