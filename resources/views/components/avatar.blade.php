{{--
    User avatar drawn by the svg-avatar package (packages/svg-avatar) in the browser:
    resources/js/jsBack/avatar.js replaces the initial with the SVG. Until then (or without JS) the initial stays.
--}}
@props(['seed', 'overrides' => null, 'initial' => '', 'size' => 34])
<span
    {{ $attributes->merge(['class' => 'adm-avatar']) }}
    data-avatar-seed="{{ $seed }}"
    data-avatar-overrides="{{ json_encode($overrides ?: new stdClass) }}"
    data-avatar-size="{{ $size }}"
    aria-hidden="true"
>{{ $initial }}</span>
