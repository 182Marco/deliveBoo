{{-- the brand: cloche mark (public/images/foodsy-mark.svg, the only copy of the logo) + wordmark --}}
@props(['inverse' => false, 'wide' => false])
<span {{ $attributes->merge(['class' => 'ui-logo']) }}>
    <img class="ui-logo-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="" width="36" height="36">
    <span class="ui-logo-word {{ $inverse ? 'ui-logo-word--inverse' : '' }} {{ $wide ? 'ui-logo-word--wide' : '' }}">Foodsy</span>
</span>
