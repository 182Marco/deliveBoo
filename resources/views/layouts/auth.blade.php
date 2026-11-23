<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#faf8f4">
    <title>@yield('title') · {{ __('partner.meta.forPartners') }}</title>
    @include('partials.favicon')
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body>
    <div class="auth">
        <aside class="auth-aside" aria-hidden="true">
            <a href="{{ url('/') }}" tabindex="-1"><x-logo inverse /></a>
            <img class="auth-aside-photo" src="https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=900&q=75" alt="" width="900" height="600">
            <div>
                <p class="auth-aside-title">{{ __('partner.auth.asideTitle') }}</p>
                <p class="auth-aside-note">{{ __('partner.auth.asideNote') }}</p>
            </div>
        </aside>

        <main class="auth-main">
            <div class="auth-top">
                <a class="adm-back auth-back" href="{{ url('/') }}">
                    <x-icon name="arrow-left" size="18" /> {{ __('partner.auth.back') }}
                </a>
                <x-locale-switch />
            </div>
            <div class="auth-intro">
                <p class="ui-eyebrow">{{ __('partner.meta.forPartners') }}</p>
                <h1 class="ui-title">@yield('heading')</h1>
                @hasSection('lead')
                    <p class="ui-lead">@yield('lead')</p>
                @endif
            </div>
            @yield('content')
        </main>
    </div>
</body>
</html>
