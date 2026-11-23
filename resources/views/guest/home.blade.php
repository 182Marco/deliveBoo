<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('site.meta.title') }}</title>
        <meta name="description" content="{{ __('site.meta.description') }}">
        <meta name="theme-color" content="#faf8f4">
        @include('partials.favicon')
        @vite('resources/js/vue.js')
    </head>
    <body>
        <div id="root"></div>
    </body>
</html>
