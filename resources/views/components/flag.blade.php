{{-- flag of a language (3:2): same shapes as the Vue Flag (resources/icons/flags.json), read once per request --}}
@props(['code', 'size' => 20])
@php($shapes = once(fn () => json_decode(file_get_contents(resource_path('icons/flags.json')), true))[$code] ?? [])
<span {{ $attributes->merge(['class' => 'ui-flag']) }}><svg class="ui-flag-svg" viewBox="0 0 30 20" width="{{ $size }}" height="{{ round($size * 2 / 3) }}" aria-hidden="true" focusable="false">@foreach ($shapes as $shape)<path d="{{ $shape['d'] }}" fill="{{ $shape['fill'] ?? 'none' }}" @isset($shape['stroke']) stroke="{{ $shape['stroke'] }}" stroke-width="{{ $shape['width'] }}" @endisset/>@endforeach</svg></span>
