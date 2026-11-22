<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#faf8f4">
    <title>@yield('title') · Foodsy for partners</title>
    @include('partials.favicon')
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body>
    <div class="auth">
        <aside class="auth-aside" aria-hidden="true">
            <a href="{{ url('/') }}" tabindex="-1"><x-logo inverse /></a>
            <img class="auth-aside-photo" src="https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=900&q=75" alt="" width="900" height="600">
            <div>
                <p class="auth-aside-title">Your kitchen, on every table in town.</p>
                <p class="auth-aside-note">Publish your menu, receive paid orders and follow your growth from one place.</p>
            </div>
        </aside>

        <main class="auth-main">
            <a class="adm-back" href="{{ url('/') }}">
                <x-icon name="arrow-left" size="18" /> Back to Foodsy
            </a>
            <div class="auth-intro">
                <p class="ui-eyebrow">Foodsy for partners</p>
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
