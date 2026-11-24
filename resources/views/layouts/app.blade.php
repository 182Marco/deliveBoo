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
    {{-- the signed-in frame: the partner hub, and the pages every role shares (the avatar editor):
         a customer sees no partner badge nor restaurant links, but their own orders --}}
    @php($partner = auth()->user()?->hasRole(\App\User::RESTAURATEUR) ?? false)
    @php($customer = auth()->user()?->hasRole(\App\User::CUSTOMER) ?? false)
    <a class="adm-skip" href="#main">{{ __('site.skip') }}</a>
    <header class="adm-header">
        <div class="ui-container adm-header-inner">
            <a class="adm-brand" href="{{ auth()->check() ? url(auth()->user()->homePath()) : url('/') }}" aria-label="{{ auth()->check() && ! $partner ? __('site.nav.home') : __('partner.header.brand') }}">
                {{-- crowded bar: only the mark under 600px, like the platform backoffice --}}
                <x-logo wide />
                @unless (auth()->check() && ! $partner)
                    <span class="adm-brand-tag">{{ __('partner.header.tag') }}</span>
                @endunless
            </a>

            <nav class="adm-nav" aria-label="{{ auth()->check() && ! $partner ? __('site.nav.main') : __('partner.header.nav') }}">
                @if ($partner)
                    <a class="adm-nav-link {{ request()->routeIs('admin.*') && ! request()->routeIs('admin.stats.*') ? 'adm-nav-link--active' : '' }}" href="{{ route('admin.restaurants.index') }}">{{ __('partner.header.restaurants') }}</a>
                    <a class="adm-nav-link {{ request()->routeIs('admin.stats.*') ? 'adm-nav-link--active' : '' }}" href="{{ route('admin.stats.index') }}">{{ __('partner.header.statistics') }}</a>
                @elseif ($customer)
                    <a class="adm-nav-link" href="{{ url('/my-orders') }}">{{ __('site.account.orders') }}</a>
                @endif
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
                            @if ($partner)
                                <a class="adm-menu-item" href="{{ route('admin.restaurants.index') }}">
                                    <x-icon name="store" size="18" /> {{ __('partner.header.myRestaurants') }}
                                </a>
                                <a class="adm-menu-item" href="{{ route('admin.stats.index') }}">
                                    <x-icon name="chart" size="18" /> {{ __('partner.header.statistics') }}
                                </a>
                            @elseif ($customer)
                                <a class="adm-menu-item" href="{{ url('/my-orders') }}">
                                    <x-icon name="receipt" size="18" /> {{ __('site.account.orders') }}
                                </a>
                            @endif
                            <a class="adm-menu-item" href="{{ route('account') }}">
                                <x-icon name="shield" size="18" /> {{ __('partner.account.menu') }}
                            </a>
                            <a class="adm-menu-item" href="{{ route('account.avatar.edit') }}">
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
