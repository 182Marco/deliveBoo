@extends('layouts.platform')

@php
    $t = fn (string $key, array $r = []) => __("platform.tables.{$key}", $r);
    $c = fn (string $key, array $r = []) => __("platform.charts.{$key}", $r);
    $money = fn ($v) => $v === null ? '–' : '€'.number_format($v, 2);
    $change = fn (string $k) => is_numeric($previous[$k] ?? null) && (float) $previous[$k] !== 0.0 && $totals[$k] !== null ? round(($totals[$k] - $previous[$k]) / abs($previous[$k]), 4) : null;
    $prev = $period->previous();
@endphp

@section('title', $restaurant->name)
@section('heading', $restaurant->name)
@section('lead', $restaurant->city.($restaurant->types->isNotEmpty() ? ' · '.$restaurant->types->pluck('name')->join(', ') : ''))

@section('content')
    <a class="adm-back" href="{{ route('platform.tables', ['section' => 'restaurants', ...$period->query()]) }}"><x-icon name="arrow-left" size="18" /> {{ $t('back') }}</a>
    <x-platform.filters :period="$period" />

    <p class="plat-note">{{ $t('restaurant.inPeriod') }} · {{ $c('changesVs', ['from' => $prev->from->isoFormat('D MMM YYYY'), 'to' => $prev->to->isoFormat('D MMM YYYY')]) }}</p>
    <div class="plat-kpis">
        <x-platform.kpi :label="$c('kpi.gross')" :value="$money($totals['gross_value'])" :change="$change('gross_value')" compare="" />
        <x-platform.kpi :label="$c('kpi.revenue')" :value="$money($totals['revenue'])" :change="$change('revenue')" compare="" estimate />
        <x-platform.kpi :label="$c('kpi.completed')" :value="number_format($totals['completed_orders'])" :change="$change('completed_orders')" compare="" />
        <x-platform.kpi :label="$c('kpi.average')" :value="$money($totals['average_order'])" :change="$change('average_order')" compare="" />
    </div>

    <div class="plat-detail">
        <section class="plat-panel">
            <h2 class="plat-panel-title">{{ $t('restaurant.byUnit', ['unit' => $c("unit.{$unit}")]) }}</h2>
            <div class="ui-table-wrap plat-side-table">
                <table class="ui-table">
                    <thead><tr><th class="ui-th">{{ $t('col.period') }}</th><th class="ui-th ui-th--num">{{ $t('col.gross') }}</th><th class="ui-th ui-th--num">{{ $t('col.orders') }}</th><th class="ui-th ui-th--num">{{ $t('col.cancelled') }}</th></tr></thead>
                    <tbody>
                        @foreach ($series as $p)
                            <tr><td class="ui-td">{{ \App\Http\Controllers\Platform\ChartController::bucketLabel($p['bucket'], $unit) }}</td><td class="ui-td ui-td--num">{{ $money($p['gross_value']) }}</td><td class="ui-td ui-td--num">{{ $p['completed_orders'] }}</td><td class="ui-td ui-td--num">{{ $p['cancelled_orders'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="plat-side">
            <section class="plat-panel">
                <dl class="plat-facts">
                    <dt class="plat-fact-label">{{ $t('restaurant.owner') }}</dt>
                    <dd class="plat-fact-value">{{ $restaurant->user ? $restaurant->user->name.' '.$restaurant->user->last_name : '–' }}</dd>
                    <dt class="plat-fact-label">{{ $t('col.rating') }}</dt>
                    <dd class="plat-fact-value">{{ $rating->n ? $t('restaurant.rating', ['avg' => number_format($rating->avg, 2), 'count' => $rating->n]) : $t('restaurant.noRating') }}</dd>
                </dl>
                <a class="ui-link" href="{{ url('/chosen-restaurant-menu-and-details/'.$restaurant->slug) }}" target="_blank" rel="noopener">{{ $t('restaurant.openSite') }} <x-icon name="external" size="14" /></a>
            </section>

            <section class="plat-panel">
                <h2 class="plat-panel-title">{{ $t('restaurant.plates') }}</h2>
                @if ($plates === [])
                    <p class="plat-note">{{ $t('restaurant.noPlates') }}</p>
                @else
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead><tr><th class="ui-th">{{ $t('order.dish') }}</th><th class="ui-th ui-th--num">{{ $t('restaurant.quantity') }}</th><th class="ui-th ui-th--num">{{ $t('restaurant.value') }}</th></tr></thead>
                            <tbody>
                                @foreach ($plates as $p)
                                    <tr><td class="ui-td">{{ $p['name'] }}</td><td class="ui-td ui-td--num">{{ $p['quantity'] }}</td><td class="ui-td ui-td--num">{{ $money($p['value']) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
