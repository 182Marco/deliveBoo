@extends('layouts.platform')

@section('title', __('platform.tabs.maps'))
@section('heading', __('platform.maps.title'))
@section('lead', __('platform.maps.lead'))

@php
    $m = fn (string $key, array $r = []) => __("platform.maps.{$key}", $r);
    $money = fn ($v) => '€'.number_format($v, 2);
    $total = array_sum(array_column($map['zones'], 'gross_value'));
    // texts of the map (popups, layers), read by resources/js/jsBack/platform/map/platformMap.js
    $mapText = [
        'restaurants' => $m('map.restaurants'),
        'zones' => $m('map.zones'),
        'gross' => __('platform.charts.col.gross'),
        'orders' => __('platform.charts.col.orders'),
        'average' => __('platform.charts.col.average'),
        'revenue' => __('platform.charts.col.revenue'),
        'rating' => __('platform.charts.col.rating'),
        'restaurantsInZone' => $m('map.restaurantsInZone'),
        'error' => $m('map.error'),
        'tilesBlocked' => $m('map.tilesBlocked'),
    ];
@endphp

@push('scripts')
    @vite('resources/js/jsBack/platform/maps.js')
@endpush

@section('content')
    <x-platform.filters :period="$period" />
    <p class="plat-note">{{ __('platform.filters.zoneNote') }} {{ $m('map.legend') }}</p>

    @if ($map['restaurants'] === [] && $map['missing']['count'] === 0)
        <div class="plat-empty">{{ $m('empty') }}</div>
    @else
        <div class="plat-map-layout">
            {{-- Leaflet is imported only when this box becomes visible (jsBack/platform/maps.js) --}}
            <div
                id="platform-map"
                class="plat-map"
                role="region"
                aria-label="{{ $m('map.label') }}"
                data-src="{{ route('platform.data.map', $period->query()) }}"
                data-text="{{ json_encode($mapText) }}"
            >
                <p class="plat-map-status">{{ $m('map.loading') }}</p>
            </div>

            <aside class="plat-side">
                <section class="plat-panel">
                    <h2 class="plat-panel-title">{{ $m('zones.title') }}</h2>
                    <p class="plat-note">{{ $m('map.located', ['count' => count($map['restaurants'])]) }}</p>
                    <div class="ui-table-wrap plat-side-table">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th class="ui-th">{{ __('platform.charts.col.city') }}</th>
                                    <th class="ui-th ui-th--num">{{ __('platform.charts.col.gross') }}</th>
                                    <th class="ui-th ui-th--num">{{ $m('zones.share') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($map['zones'] as $zone)
                                    <tr>
                                        <td class="ui-td">
                                            <a class="ui-link" href="{{ route('platform.maps', $period->query(['city' => $zone['zone']])) }}">{{ $zone['zone'] }}</a>
                                            <span class="plat-cell-note">{{ $m('map.restaurantsInZone', ['count' => $zone['restaurants']]) }}</span>
                                        </td>
                                        <td class="ui-td ui-td--num">{{ $money($zone['gross_value']) }}</td>
                                        <td class="ui-td ui-td--num">{{ $total > 0 ? number_format(100 * $zone['gross_value'] / $total, 1).'%' : '–' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="plat-panel">
                    <h2 class="plat-panel-title">{{ $m('missing.title') }} ({{ $map['missing']['count'] }})</h2>
                    @if ($map['missing']['count'] === 0)
                        <p class="plat-note">{{ $m('missing.none') }}</p>
                    @else
                        <p class="plat-note">{{ $m('missing.text', ['count' => $map['missing']['count']]) }}</p>
                        <ul class="plat-missing">
                            @foreach ($map['missing']['items'] as $name)
                                <li>{{ $name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </aside>
        </div>
    @endif
@endsection
