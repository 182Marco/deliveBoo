@extends('layouts.auth')

@section('title', 'Join Foodsy')
@section('heading', 'Bring your kitchen to Foodsy')
@section('lead', 'Create your partner account: you can publish your first restaurant in minutes.')

@section('content')
    <form class="adm-form" method="POST" action="{{ route('register') }}">
        @csrf
        <div class="adm-form-grid">
            <x-field name="name" label="First name" :value="old('name')" required autocomplete="given-name" autofocus />
            <x-field name="last_name" label="Last name" :value="old('last_name')" required autocomplete="family-name" />
            <x-field name="email" label="Email" type="email" :value="old('email')" required autocomplete="email" wide />
            <x-field name="vat_number" label="VAT number" :value="old('vat_number')" required wide />
            <x-field name="password" label="Password" type="password" required autocomplete="new-password" />
            <x-field name="password_confirmation" id="password-confirm" label="Confirm password" type="password" required autocomplete="new-password" />
        </div>

        <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">Create partner account</button>

        <p class="auth-switch">
            Already a partner? <a class="ui-link" href="{{ route('login') }}">Log in</a>
        </p>
    </form>
@endsection
