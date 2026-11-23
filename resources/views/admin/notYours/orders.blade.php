@extends('layouts.app')

@section('title', __('partner.notYours.title'))

@section('content')
<div class="ui-container ui-container--narrow">
    <div class="ui-empty">
        <img class="ui-empty-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="">
        <h1 class="ui-subtitle">{{ __('partner.notYours.orders') }}</h1>
        <p class="ui-muted">{{ __('partner.notYours.text') }}</p>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.restaurants.index') }}">{{ __('partner.notYours.cta') }}</a>
    </div>
</div>
@endsection
