@extends('layouts.auth')

@section('title', __('partner.auth.confirm.title'))
@section('heading', __('partner.auth.confirm.heading'))
@section('lead', __('partner.auth.confirm.lead'))

@section('content')
    <form class="adm-form" method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <x-field name="password" :label="__('partner.auth.password')" type="password" required autocomplete="current-password" />
        <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">{{ __('partner.auth.confirm.submit') }}</button>
        @if (Route::has('password.request'))
            <p class="auth-switch"><a class="ui-link" href="{{ route('password.request') }}">{{ __('partner.auth.forgot') }}</a></p>
        @endif
    </form>
@endsection
