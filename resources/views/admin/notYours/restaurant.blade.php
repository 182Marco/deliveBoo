@extends('layouts.app')

@section('title', 'Not available')

@section('content')
<div class="ui-container ui-container--narrow">
    <div class="ui-empty">
        <img class="ui-empty-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="">
        <h1 class="ui-subtitle">This restaurant belongs to another partner</h1>
        <p class="ui-muted">You can only manage your own restaurants. It was probably just a wrong link.</p>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.restaurants.index') }}">Go to your restaurants</a>
    </div>
</div>
@endsection
