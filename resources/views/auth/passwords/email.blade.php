@extends('layouts.auth')

@section('title', 'Reset password')
@section('heading', 'Reset your password')
@section('lead', 'Enter the email of your partner account: we will send you a link to choose a new password.')

@section('content')
    @if (session('status'))
        <div class="ui-notice ui-notice--success" role="status">{{ session('status') }}</div>
    @endif

    <form class="adm-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="email" autofocus />
        <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">Send reset link</button>
        <p class="auth-switch"><a class="ui-link" href="{{ route('login') }}">Back to log in</a></p>
    </form>
@endsection
