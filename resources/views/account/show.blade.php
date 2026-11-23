@extends('layouts.auth')

{{-- "Your account" for every role: details, Google link, and the VAT number a restaurateur created
     through Google still has to add (the partner hub waits for it: middleware profile.complete). --}}
@php($a = fn (string $key) => __("partner.account.{$key}"))
@php($incomplete = $user->role === \App\User::RESTAURATEUR && $user->vat_number === null)

@section('title', $a('title'))
@section('metaSuffix', 'Foodsy')
@section('eyebrow', __('partner.auth.account'))
@section('heading', $incomplete ? $a('completeTitle') : $a('title'))
@section('lead', $incomplete ? $a('completeLead') : $a('lead'))
@section('asidePhoto', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=900&q=75')
@section('asideTitle', __('partner.auth.customerAsideTitle'))
@section('asideNote', __('partner.auth.customerAsideNote'))

@section('content')
    @if (session('status'))
        <div class="ui-notice ui-notice--success" role="status">{{ session('status') }}</div>
    @endif

    @if ($incomplete)
        <form class="adm-form" method="POST" action="{{ route('account.profile') }}">
            @csrf
            <x-field name="vat_number" :label="__('partner.auth.register.vat')" :value="old('vat_number')" required inputmode="numeric" />
            <button type="submit" class="ui-btn ui-btn--primary ui-btn--lg ui-btn--block">{{ $a('save') }}</button>
        </form>
    @endif

    <dl class="auth-facts">
        <dt class="auth-fact-label">{{ __('platform.users.col.name') }}</dt>
        <dd class="auth-fact-value">{{ $user->name }} {{ $user->last_name }}</dd>
        <dt class="auth-fact-label">{{ __('partner.auth.email') }}</dt>
        <dd class="auth-fact-value">{{ $user->email }}</dd>
        <dt class="auth-fact-label">{{ $a('role') }}</dt>
        <dd class="auth-fact-value">{{ __("platform.users.role.{$user->role}") }}</dd>
    </dl>

    <section class="auth-demo">
        <h2 class="auth-demo-title">{{ $a('google') }}</h2>
        @if ($user->isAdmin())
            <p class="auth-demo-text">{{ $a('googleAdmin') }}</p>
        @elseif ($user->google_id)
            <p class="auth-demo-text">{{ $a('googleLinked') }}</p>
            <form method="POST" action="{{ route('account.google.unlink') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="ui-btn ui-btn--outline ui-btn--sm">{{ $a('unlink') }}</button>
            </form>
        @elseif ($googleEnabled)
            <p class="auth-demo-text">{{ $a('googleNotLinked') }}</p>
            <a class="ui-btn ui-btn--outline ui-btn--sm" href="{{ route('account.google.link') }}">{{ $a('link') }}</a>
        @else
            <p class="auth-demo-text">{{ $a('googleOff') }}</p>
        @endif
        @error('google')
            <p class="ui-field-error" role="alert">{{ $message }}</p>
        @enderror
    </section>

    @unless ($incomplete || $user->role === \App\User::CUSTOMER)
        <a class="ui-btn ui-btn--primary ui-btn--block" href="{{ url($user->homePath()) }}">{{ $a('open') }}: {{ $user->isAdmin() ? __('site.account.platform') : __('site.account.partnerHub') }}</a>
    @endunless
@endsection
