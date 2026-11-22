<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#faf8f4">
    <title>@yield('title', 'Partner hub') · Foodsy</title>
    @include('partials.favicon')
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body>
    <a class="adm-skip" href="#main">Skip to content</a>
    <header class="adm-header">
        <div class="ui-container adm-header-inner">
            <a class="adm-brand" href="{{ auth()->check() ? route('admin.restaurants.index') : url('/') }}" aria-label="Foodsy partner hub">
                <x-logo />
                <span class="adm-brand-tag">Partner</span>
            </a>

            <nav class="adm-nav" aria-label="Partner">
                @auth
                    <a class="adm-nav-link {{ request()->routeIs('admin.*') ? 'adm-nav-link--active' : '' }}" href="{{ route('admin.restaurants.index') }}">Restaurants</a>
                @endauth
                <a class="adm-nav-link" href="{{ url('/') }}" target="_blank" rel="noopener">
                    View site <x-icon name="external" size="16" />
                </a>
            </nav>

            <div class="adm-account">
                @guest
                    <a class="ui-btn ui-btn--ghost ui-btn--sm" href="{{ route('login') }}">Log in</a>
                    @if (Route::has('register'))
                        <a class="ui-btn ui-btn--primary ui-btn--sm" href="{{ route('register') }}">Join Foodsy</a>
                    @endif
                @else
                    <details class="adm-menu">
                        <summary class="adm-menu-trigger">
                            <span class="adm-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                            <span class="adm-menu-name">{{ Auth::user()->name }}</span>
                            <x-icon name="chevron-down" size="16" />
                        </summary>
                        <div class="adm-menu-panel">
                            <a class="adm-menu-item" href="{{ route('admin.restaurants.index') }}">
                                <x-icon name="store" size="18" /> My restaurants
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="adm-menu-item">
                                    <x-icon name="logout" size="18" /> Log out
                                </button>
                            </form>
                        </div>
                    </details>
                @endguest
            </div>
        </div>
    </header>

    <main id="main" class="adm-main" tabindex="-1">
        @yield('content')
    </main>
</body>
</html>
