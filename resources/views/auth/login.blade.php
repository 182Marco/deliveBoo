@extends('layouts.auth')

@section('title', 'Log in')
@section('heading', 'Welcome back')
@section('lead', 'Log in to manage your restaurants, menus and orders.')

@section('content')
    <form class="adm-form" method="POST" action="{{ route('login') }}">
        @csrf
        <x-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="email" autofocus />
        <x-field name="password" label="Password" type="password" required autocomplete="current-password" />

        <label class="ui-check">
            <input class="ui-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            Keep me logged in
        </label>

        {{-- #logBtn: resources/js/jsBack/logListen.js shows the "logged in" message on the next page --}}
        <button type="submit" id="logBtn" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">Log in</button>

        <p class="auth-switch">
            @if (Route::has('password.request'))
                <a class="ui-link" href="{{ route('password.request') }}">Forgot your password?</a>
                ·
            @endif
            New to Foodsy? <a class="ui-link" href="{{ route('register') }}">Add your restaurant</a>
        </p>
    </form>
    @vite('resources/js/jsBack/logListen.js')
@endsection
