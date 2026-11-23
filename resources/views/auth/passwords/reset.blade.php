@extends('layouts.auth')

@section('title', __('partner.auth.reset.title'))
@section('heading', __('partner.auth.reset.title'))

@section('content')
    <form class="adm-form" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-field name="email" :label="__('partner.auth.email')" type="email" :value="$email ?? old('email')" required autocomplete="email" autofocus />
        <x-field name="password" :label="__('partner.auth.reset.password')" type="password" required autocomplete="new-password" />
        <x-field name="password_confirmation" id="password-confirm" :label="__('partner.auth.reset.confirm')" type="password" required autocomplete="new-password" />
        <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">{{ __('partner.auth.reset.submit') }}</button>
    </form>
@endsection
