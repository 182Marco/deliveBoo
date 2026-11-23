<!doctype html>
{{-- Platform backoffice (/platform, admins only): one layout and one authorization for the four tabs.
     The rosemary band of the tabs tells it apart from the customer site and the partner hub. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#faf8f4">
    <title>@yield('title') · {{ __('platform.meta.title') }} · Foodsy</title>
    @include('partials.favicon')
    @vite(['resources/css/platform.css', 'resources/js/app.js', 'resources/js/jsBack/avatar.js'])
    @stack('scripts')
</head>
<body class="plat-body">
    <a class="adm-skip" href="#main">{{ __('site.skip') }}</a>
    <header class="plat-header">
        <div class="ui-container plat-header-inner">
            <a class="plat-brand" href="{{ route('platform.home') }}" aria-label="{{ __('platform.header.brand') }}">
                {{-- the wordmark from 600px: on phones mark + Platform badge + language + account fit one row --}}
                <x-logo wide />
                <span class="plat-brand-tag">{{ __('platform.header.tag') }}</span>
            </a>

            <div class="plat-account">
                <a class="plat-header-link" href="{{ url('/') }}" target="_blank" rel="noopener">
                    {{ __('platform.header.viewSite') }} <x-icon name="external" size="16" />
                </a>
                <x-locale-switch />
                <details class="adm-menu">
                    <summary class="adm-menu-trigger" aria-label="{{ __('platform.header.account') }}">
                        <x-avatar :seed="Auth::user()->avatarSeed()" :overrides="Auth::user()->avatarOverrides()" :initial="mb_strtoupper(mb_substr(Auth::user()->name, 0, 1))" />
                        <span class="adm-menu-name">{{ Auth::user()->name }}</span>
                        <x-icon name="chevron-down" size="16" />
                    </summary>
                    <div class="adm-menu-panel">
                        <a class="adm-menu-item" href="{{ route('account') }}">
                            <x-icon name="shield" size="18" /> {{ __('partner.account.menu') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="adm-menu-item">
                                <x-icon name="logout" size="18" /> {{ __('platform.header.logout') }}
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </div>

        <div class="plat-band">
        <nav class="ui-container plat-tabs" aria-label="{{ __('platform.header.tabs') }}">
            @foreach (['maps' => ['platform.maps', 'map'], 'charts' => ['platform.charts', 'chart'], 'tables' => ['platform.tables', 'table'], 'users' => ['platform.users.index', 'users']] as $tab => [$route, $icon])
                @php($active = request()->routeIs("platform.{$tab}*"))
                <a
                    @class(['plat-tab', 'plat-tab--active' => $active])
                    href="{{ route($route, $tab === 'users' ? [] : request()->only(['from', 'to', 'city', 'type'])) }}"
                    @if ($active) aria-current="page" @endif
                >
                    <x-icon :name="$icon" size="18" /> {{ __("platform.tabs.{$tab}") }}
                </a>
            @endforeach
        </nav>
        </div>
    </header>

    <main id="main" class="plat-main" tabindex="-1">
        <div class="ui-container plat-page">
            <div class="plat-intro">
                <h1 class="ui-title plat-title">@yield('heading')</h1>
                @hasSection('lead')
                    <p class="plat-lead">@yield('lead')</p>
                @endif
            </div>
            @if (session('status'))
                <div class="ui-notice ui-notice--success" role="status">{{ session('status') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>
