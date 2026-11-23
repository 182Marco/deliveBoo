@extends('layouts.auth')

@section('title', __('partner.auth.register.title'))
@section('heading', __('partner.auth.register.heading'))
@section('lead', __('partner.auth.register.lead'))

@section('content')
    <form class="adm-form" method="POST" action="{{ route('register') }}">
        @csrf
        <div class="adm-form-grid">
            <x-field name="name" :label="__('partner.auth.register.firstName')" :value="old('name')" required autocomplete="given-name" autofocus />
            <x-field name="last_name" :label="__('partner.auth.register.lastName')" :value="old('last_name')" required autocomplete="family-name" />
            <x-field name="email" :label="__('partner.auth.email')" type="email" :value="old('email')" required autocomplete="email" wide />
            <x-field name="vat_number" :label="__('partner.auth.register.vat')" :value="old('vat_number')" required wide />
            <x-field name="password" :label="__('partner.auth.password')" type="password" required autocomplete="new-password" />
            <x-field name="password_confirmation" id="password-confirm" :label="__('partner.auth.register.confirmPassword')" type="password" required autocomplete="new-password" />
        </div>

        <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">{{ __('partner.auth.register.submit') }}</button>

        <p class="auth-switch">
            {{ __('partner.auth.register.already') }} <a class="ui-link" href="{{ route('login') }}">{{ __('partner.auth.register.login') }}</a>
        </p>
    </form>
@endsection
