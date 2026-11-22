{{-- photo of a restaurant or dish: full URLs (seed data) or uploads in storage; the brand mark when missing or broken --}}
@props(['src' => null, 'alt' => ''])
@php($url = $src ? (\Illuminate\Support\Str::startsWith($src, ['http://', 'https://', '/']) ? $src : asset('storage/' . $src)) : null)
<div {{ $attributes->merge(['class' => 'ui-photo']) }}>
    <img class="ui-photo-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="">
    @if ($url)
        <img class="ui-photo-img" src="{{ $url }}" alt="{{ $alt }}" loading="lazy" decoding="async" onerror="this.hidden = true">
    @endif
</div>
