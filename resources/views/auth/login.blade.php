@extends('layouts.auth')

@section('title', __('partner.auth.login.title'))
@section('metaSuffix', 'Foodsy')
@section('eyebrow', __('partner.auth.account'))
@section('asidePhoto', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=900&q=75')
@section('asideTitle', __('partner.auth.customerAsideTitle'))
@section('asideNote', __('partner.auth.customerAsideNote'))
@section('heading', __('partner.auth.login.heading'))
@section('lead', __('partner.auth.login.lead'))

@section('content')
    <form class="adm-form" method="POST" action="{{ route('login') }}">
        @csrf
        <x-field name="email" :label="__('partner.auth.email')" type="email" :value="old('email')" required autocomplete="email" autofocus />
        <x-field name="password" :label="__('partner.auth.password')" type="password" required autocomplete="current-password" />

        <label class="ui-check">
            <input class="ui-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            {{ __('partner.auth.login.remember') }}
        </label>

        {{-- #logBtn: resources/js/jsBack/logListen.js shows the "logged in" message on the next page --}}
        <button type="submit" id="logBtn" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">{{ __('partner.auth.login.submit') }}</button>

        <p class="auth-switch">
            @if (Route::has('password.request'))
                <a class="ui-link" href="{{ route('password.request') }}">{{ __('partner.auth.forgot') }}</a>
                ·
            @endif
            {{ __('partner.auth.login.newHere') }} <a class="ui-link" href="{{ route('register') }}">{{ __('partner.auth.login.register') }}</a>
        </p>
    </form>

    <x-demo-accounts />
    @vite('resources/js/jsBack/logListen.js')
@endsection
