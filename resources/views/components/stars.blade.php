{{-- read-only stars of a rating (0-5, decimals rounded to half a star), same look as StarsBox in Vue --}}
@props(['value' => 0, 'size' => 16])
@php
    $path = once(fn () => json_decode(file_get_contents(resource_path('icons/icons.json')), true))['star'];
    $half = round(((float) $value) * 2) / 2;
    $star = fn (bool $on) => '<svg class="ui-icon '.($on ? 'ui-star ui-star--on' : 'ui-star').'" viewBox="0 0 24 24" width="'.$size.'" height="'.$size.'" fill="'.($on ? 'currentColor' : 'none').'" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="'.$path.'"/></svg>';
@endphp
<span {{ $attributes->merge(['class' => 'ui-stars']) }} role="img" aria-label="{{ __('site.stars.rated', ['average' => number_format((float) $value, 1)]) }}">
    @for ($n = 1; $n <= 5; $n++)
        @if ($half >= $n)
            {!! $star(true) !!}
        @elseif ($half >= $n - 0.5)
            <span class="ui-star-half">{!! $star(false) !!}<span class="ui-star-half-fill">{!! $star(true) !!}</span></span>
        @else
            {!! $star(false) !!}
        @endif
    @endfor
</span>
