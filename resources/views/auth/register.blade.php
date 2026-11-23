@extends('layouts.auth')

{{-- one sign-up page, two account types: $role comes from RegisterController@showRegistrationForm
     (?role=restaurateur from the partner CTAs, customer by default). Admin is never offered here. --}}
@php($partner = $role === \App\User::RESTAURATEUR)

@section('title', $partner ? __('partner.auth.register.title') : __('partner.auth.register.customer.title'))
@section('heading', $partner ? __('partner.auth.register.heading') : __('partner.auth.register.customer.heading'))
@section('lead', $partner ? __('partner.auth.register.lead') : __('partner.auth.register.customer.lead'))
@unless ($partner)
    @section('metaSuffix', 'Foodsy')
    @section('eyebrow', __('partner.auth.account'))
    @section('asidePhoto', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=900&q=75')
    @section('asideTitle', __('partner.auth.customerAsideTitle'))
    @section('asideNote', __('partner.auth.customerAsideNote'))
@endunless

@section('content')
    {{-- the tabs are links: no script, and the page always matches the role sent by the form --}}
    <nav class="auth-tabs" aria-label="{{ __('partner.auth.register.tabs.label') }}">
        @foreach (\App\User::SIGNUP_ROLES as $tab)
            <a
                @class(['auth-tab', 'auth-tab--active' => $tab === $role])
                href="{{ route('register', ['role' => $tab]) }}"
                @if ($tab === $role) aria-current="page" @endif
            >
                <x-icon :name="$tab === \App\User::RESTAURATEUR ? 'store' : 'bag'" size="18" />
                {{ __('partner.auth.register.tabs.'.$tab) }}
            </a>
        @endforeach
    </nav>

    <form class="adm-form" method="POST" action="{{ route('register') }}">
        @csrf
        <input type="hidden" name="role" value="{{ $role }}">
        <div class="adm-form-grid">
            <x-field name="name" :label="__('partner.auth.register.firstName')" :value="old('name')" required autocomplete="given-name" autofocus />
            <x-field name="last_name" :label="__('partner.auth.register.lastName')" :value="old('last_name')" required autocomplete="family-name" />
            <x-field name="email" :label="__('partner.auth.email')" type="email" :value="old('email')" required autocomplete="email" wide />
            @if ($partner)
                <x-field name="vat_number" :label="__('partner.auth.register.vat')" :value="old('vat_number')" required inputmode="numeric" wide />
            @endif
            <x-field name="password" :label="__('partner.auth.password')" type="password" required autocomplete="new-password" />
            <x-field name="password_confirmation" id="password-confirm" :label="__('partner.auth.register.confirmPassword')" type="password" required autocomplete="new-password" />
        </div>

        <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">
            {{ $partner ? __('partner.auth.register.submit') : __('partner.auth.register.customer.submit') }}
        </button>

        <p class="auth-switch">
            {{ __('partner.auth.register.already') }} <a class="ui-link" href="{{ route('login') }}">{{ __('partner.auth.register.login') }}</a>
        </p>
    </form>
@endsection
