<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#faf8f4">
    <title>@yield('title', __('partner.meta.hub')) · Foodsy</title>
    @include('partials.favicon')
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
    @auth
        @vite('resources/js/jsBack/avatar.js')
    @endauth
</head>
<body>
    <a class="adm-skip" href="#main">{{ __('site.skip') }}</a>
    <header class="adm-header">
        <div class="ui-container adm-header-inner">
            <a class="adm-brand" href="{{ auth()->check() ? route('admin.restaurants.index') : url('/') }}" aria-label="{{ __('partner.header.brand') }}">
                <x-logo />
                <span class="adm-brand-tag">{{ __('partner.header.tag') }}</span>
            </a>

            <nav class="adm-nav" aria-label="{{ __('partner.header.nav') }}">
                @auth
                    <a class="adm-nav-link {{ request()->routeIs('admin.*') ? 'adm-nav-link--active' : '' }}" href="{{ route('admin.restaurants.index') }}">{{ __('partner.header.restaurants') }}</a>
                @endauth
                <a class="adm-nav-link" href="{{ url('/') }}" target="_blank" rel="noopener">
                    {{ __('partner.header.viewSite') }} <x-icon name="external" size="16" />
                </a>
            </nav>

            <div class="adm-account">
                <x-locale-switch />
                @guest
                    <a class="ui-btn ui-btn--ghost ui-btn--sm" href="{{ route('login') }}">{{ __('partner.header.login') }}</a>
                    @if (Route::has('register'))
                        <a class="ui-btn ui-btn--primary ui-btn--sm" href="{{ route('register') }}">{{ __('partner.header.join') }}</a>
                    @endif
                @else
                    <details class="adm-menu">
                        <summary class="adm-menu-trigger">
                            <x-avatar :seed="Auth::user()->avatarSeed()" :overrides="Auth::user()->avatarOverrides()" :initial="mb_strtoupper(mb_substr(Auth::user()->name, 0, 1))" />
                            <span class="adm-menu-name">{{ Auth::user()->name }}</span>
                            <x-icon name="chevron-down" size="16" />
                        </summary>
                        <div class="adm-menu-panel">
                            <a class="adm-menu-item" href="{{ route('admin.restaurants.index') }}">
                                <x-icon name="store" size="18" /> {{ __('partner.header.myRestaurants') }}
                            </a>
                            <a class="adm-menu-item" href="{{ route('admin.avatar.edit') }}">
                                <x-icon name="user" size="18" /> {{ __('partner.header.customizeAvatar') }}
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="adm-menu-item">
                                    <x-icon name="logout" size="18" /> {{ __('partner.header.logout') }}
                                </button>
                            </form>
                        </div>
                    </details>
                @endguest
            </div>
        </div>
    </header>

    <main id="main" class="adm-main" tabindex="-1">
        @if (session('avatarSaved'))
            <div class="ui-container">
                <div class="ui-notice ui-notice--success adm-flash" role="status">{{ __('partner.avatar.saved') }}</div>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
