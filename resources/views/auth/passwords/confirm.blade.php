@extends('layouts.auth')

@section('title', 'Confirm password')
@section('heading', 'Confirm your password')
@section('lead', 'This is a protected area: please confirm your password before continuing.')

@section('content')
    <form class="adm-form" method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <x-field name="password" label="Password" type="password" required autocomplete="current-password" />
        <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">Confirm password</button>
        @if (Route::has('password.request'))
            <p class="auth-switch"><a class="ui-link" href="{{ route('password.request') }}">Forgot your password?</a></p>
        @endif
    </form>
@endsection
