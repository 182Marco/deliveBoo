<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#faf8f4">
    <title>@yield('title') · @yield('metaSuffix', __('partner.meta.forPartners'))</title>
    @include('partials.favicon')
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body>
    {{-- "fit" (login): exactly one screen high, the photo covers the side panel and the copy sits on its fade --}}
    @php($fit = trim($__env->yieldContent('authVariant')) === 'fit')
    <div @class(['auth', 'auth--fit' => $fit])>
        <aside @class(['auth-aside', 'auth-aside--cover' => $fit]) aria-hidden="true">
            <a @class(['auth-aside-brand', 'auth-aside-brand--cover' => $fit]) href="{{ url('/') }}" tabindex="-1"><x-logo inverse /></a>
            {{-- side panel: the partner one by default, the customer one on login and customer sign-up --}}
            <img @class(['auth-aside-photo', 'auth-aside-photo--cover' => $fit]) src="@yield('asidePhoto', 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=900&q=75')" alt="" width="900" height="600">
            <div class="auth-aside-copy">
                <p class="auth-aside-title">@yield('asideTitle', __('partner.auth.asideTitle'))</p>
                <p class="auth-aside-note">@yield('asideNote', __('partner.auth.asideNote'))</p>
            </div>
        </aside>

        <main @class(['auth-main', 'auth-main--fit' => $fit])>
            <div class="auth-top">
                <a class="adm-back auth-back" href="{{ url('/') }}">
                    <x-icon name="arrow-left" size="18" /> {{ __('partner.auth.back') }}
                </a>
                <x-locale-switch />
            </div>
            <div class="auth-intro">
                <p class="ui-eyebrow">@yield('eyebrow', __('partner.meta.forPartners'))</p>
                <h1 class="ui-title">@yield('heading')</h1>
                @hasSection('lead')
                    <p @class(['ui-lead', 'auth-lead--fit' => $fit])>@yield('lead')</p>
                @endif
            </div>
            @yield('content')
        </main>
    </div>
</body>
</html>
