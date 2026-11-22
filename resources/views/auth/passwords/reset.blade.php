@extends('layouts.auth')

@section('title', 'Choose a new password')
@section('heading', 'Choose a new password')

@section('content')
    <form class="adm-form" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-field name="email" label="Email" type="email" :value="$email ?? old('email')" required autocomplete="email" autofocus />
        <x-field name="password" label="New password" type="password" required autocomplete="new-password" />
        <x-field name="password_confirmation" id="password-confirm" label="Confirm new password" type="password" required autocomplete="new-password" />
        <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">Save new password</button>
    </form>
@endsection
