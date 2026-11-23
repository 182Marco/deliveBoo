@extends('layouts.auth')

@section('title', __('partner.auth.request.title'))
@section('heading', __('partner.auth.request.heading'))
@section('lead', __('partner.auth.request.lead'))

@section('content')
    @if (session('status'))
        <div class="ui-notice ui-notice--success" role="status">{{ session('status') }}</div>
    @endif

    <form class="adm-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-field name="email" :label="__('partner.auth.email')" type="email" :value="old('email')" required autocomplete="email" autofocus />
        <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">{{ __('partner.auth.request.submit') }}</button>
        <p class="auth-switch"><a class="ui-link" href="{{ route('login') }}">{{ __('partner.auth.request.backToLogin') }}</a></p>
    </form>
@endsection
