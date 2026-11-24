{{-- same icon set as the Vue site (resources/icons/icons.json), read once per request --}}
@props(['name', 'size' => 20, 'filled' => false])
@php($path = once(fn () => json_decode(file_get_contents(resource_path('icons/icons.json')), true))[$name] ?? '')
<svg {{ $attributes->merge(['class' => 'ui-icon']) }} viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}" fill="{{ $filled ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="{{ $path }}"/></svg>
