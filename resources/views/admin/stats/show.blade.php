@extends('layouts.app')

@section('title', __('partner.common.statistics'))

{{-- Partner statistics (Admin\StatsController): all the owner's restaurants ($restaurant = null) or one.
     Only aggregates reach this page; the charts are drawn by jsBack/platform/charts.js from #platform-charts. --}}
@php
    $s = fn (string $key, array $r = []) => __("partner.stats.{$key}", $r);
    $c = fn (string $key, array $r = []) => __("platform.charts.{$key}", $r);
    $money = fn ($v) => $v === null ? '–' : '€'.number_format($v, 2);
@endphp

@section('content')
<div class="ui-container plat-stack">
    <a class="adm-back" href="{{ route('admin.restaurants.index') }}"><x-icon name="arrow-left" size="18" /> {{ __('partner.common.yourRestaurants') }}</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ $restaurant ? $restaurant->name : $s('scope.all') }}</p>
            <h1 class="ui-title">{{ $s('heading') }}</h1>
            <p class="ui-muted">{{ $s('lead') }}</p>
        </div>
        @if ($restaurant)
            <div class="adm-rest-foot">
                <a class="ui-btn ui-btn--outline ui-btn--sm" href="{{ route('admin.reviews.index', $restaurant->slug) }}"><x-icon name="star" size="16" /> {{ $s('links.reviews') }}</a>
                <a class="ui-btn ui-btn--outline ui-btn--sm" href="{{ route('admin.orders.index', $restaurant->slug) }}"><x-icon name="receipt" size="16" /> {{ $s('links.orders') }}</a>
            </div>
        @endif
    </div>

    @if ($empty)
        <div class="plat-empty">{{ $s('noRestaurants') }}</div>
    @else
        @if ($restaurants->count() > 1)
            <form class="plat-scope" method="GET" action="{{ route('admin.stats.index') }}">
                @foreach ($period->query() as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <div class="ui-field plat-scope-field">
                    <label class="ui-label" for="stats-scope">{{ $s('scope.label') }}</label>
                    <select class="ui-input" id="stats-scope" name="restaurant">
                        <option value="">{{ $s('scope.all') }} · {{ trans_choice('partner.stats.scope.count', $restaurants->count(), ['count' => $restaurants->count()]) }}</option>
                        @foreach ($restaurants as $r)
                            <option value="{{ $r->id }}" @selected($restaurant && $restaurant->id === $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="ui-btn ui-btn--dark">{{ $s('scope.label') }}</button>
            </form>
        @endif

        <x-platform.filters :period="$period" :places="false" />

        @php
            $now = $summary['current'];
            $change = $summary['change'];
            $prevFrom = \Carbon\Carbon::parse($summary['previous_period']['from'])->isoFormat('D MMM YYYY');
            $prevTo = \Carbon\Carbon::parse($summary['previous_period']['to'])->isoFormat('D MMM YYYY');
            $vs = $c('vsPrevious', ['from' => $prevFrom, 'to' => $prevTo]);
        @endphp

        @if ($now['gross_orders'] + $now['cancelled_orders'] + $now['failed_orders'] === 0)
            <div class="plat-empty">{{ $c('empty') }}</div>
        @else
            <p class="plat-note">{{ $c('changesVs', ['from' => $prevFrom, 'to' => $prevTo]) }}</p>
            <div class="plat-kpis">
                <x-platform.kpi :label="$s('kpi.gross')" :value="$money($now['gross_value'])" :change="$change['gross_value']" :compare="$vs" :hint="$c('kpi.grossHint')" />
                <x-platform.kpi :label="$c('kpi.completed')" :value="number_format($now['completed_orders'])" :change="$change['completed_orders']" :compare="$vs" :hint="$c('kpi.completedHint')" />
                <x-platform.kpi :label="$c('kpi.average')" :value="$money($now['average_order'])" :change="$change['average_order']" :compare="$vs" :hint="$c('kpi.averageHint')" />
                <x-platform.kpi :label="$c('kpi.customers')" :value="number_format($now['customers'])" :change="$change['customers']" :compare="$vs" :hint="$s('kpi.customersHint')" />
                <x-platform.kpi :label="$s('kpi.rating')" :value="$rating['average'] === null ? '–' : $s('kpi.ratingValue', ['value' => number_format($rating['average'], 2)])" :change="$ratingChange" :compare="$vs" :hint="$s('kpi.ratingHint', ['count' => number_format($rating['count'])])" />
                <x-platform.kpi :label="$c('kpi.delivery')" :value="$now['delivery_median_minutes'] === null ? '–' : $c('kpi.deliveryValue', ['minutes' => $now['delivery_median_minutes']])" :hint="$now['delivery_p90_minutes'] === null ? null : $c('kpi.deliveryHint', ['p90' => $now['delivery_p90_minutes']])" />
                <x-platform.kpi :label="$c('kpi.cancelled')" :value="number_format($now['cancelled_orders'])" :change="$change['cancelled_orders']" :up-is-good="false" :compare="$vs" :hint="$c('kpi.cancelledHint', ['value' => $money($now['cancelled_value'])])" />
                <x-platform.kpi :label="$c('kpi.failed')" :value="number_format($now['failed_orders'])" :change="$change['failed_orders']" :up-is-good="false" :compare="$vs" :hint="$c('kpi.failedHint', ['value' => $money($now['failed_value'])])" />
            </div>

            <div class="plat-charts">
                <x-platform.chart-card chart="sales" :title="$c('sales.title')" :sub="$c('sales.sub', ['unit' => $unitLabel]).($hasPartial ? ' · '.$c('partialNote', ['unit' => $unitLabel]) : '')" wide>
                    <x-slot:table>
                        <table class="ui-table">
                            <thead><tr><th class="ui-th">{{ $c('col.period') }}</th><th class="ui-th ui-th--num">{{ $s('kpi.gross') }}</th><th class="ui-th ui-th--num">{{ $c('col.orders') }}</th><th class="ui-th ui-th--num">{{ $c('col.average') }}</th><th class="ui-th ui-th--num">{{ $c('kpi.cancelled') }}</th></tr></thead>
                            <tbody>
                                @foreach ($series as $i => $p)
                                    <tr><td class="ui-td">{{ $labels[$i] }}</td><td class="ui-td ui-td--num">{{ $money($p['gross_value']) }}</td><td class="ui-td ui-td--num">{{ $p['completed_orders'] }}</td><td class="ui-td ui-td--num">{{ $money($p['average_order']) }}</td><td class="ui-td ui-td--num">{{ $p['cancelled_orders'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot:table>
                </x-platform.chart-card>

                <x-platform.chart-card chart="orders" :title="$c('orders.title')" :sub="$c('orders.sub', ['unit' => $unitLabel])" />

                <x-platform.chart-card chart="ratings" :title="$s('ratings.title')" :sub="$s('ratings.sub', ['unit' => $unitLabel])">
                    <x-slot:table>
                        <table class="ui-table">
                            <thead><tr><th class="ui-th">{{ $c('col.period') }}</th><th class="ui-th ui-th--num">{{ $s('ratings.average') }}</th><th class="ui-th ui-th--num">{{ $s('ratings.count') }}</th></tr></thead>
                            <tbody>
                                @foreach ($ratings as $i => $p)
                                    <tr><td class="ui-td">{{ $labels[$i] }}</td><td class="ui-td ui-td--num">{{ $p['average'] === null ? '–' : number_format($p['average'], 2) }}</td><td class="ui-td ui-td--num">{{ $p['count'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot:table>
                </x-platform.chart-card>

                <x-platform.chart-card chart="delivery" :title="$s('delivery.title')" :sub="$s('delivery.sub', ['unit' => $unitLabel])">
                    <x-slot:table>
                        <table class="ui-table">
                            <thead><tr><th class="ui-th">{{ $c('col.period') }}</th><th class="ui-th ui-th--num">{{ $s('delivery.minutes') }}</th></tr></thead>
                            <tbody>
                                @foreach ($delivery as $i => $p)
                                    <tr><td class="ui-td">{{ $labels[$i] }}</td><td class="ui-td ui-td--num">{{ $p['minutes'] ?? '–' }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot:table>
                </x-platform.chart-card>

                <x-platform.chart-card chart="compare" :title="$c('compare.title')" :sub="$c('compare.sub', ['unit' => $unitLabel, 'from' => $prevFrom, 'to' => $prevTo])" wide />

                <x-platform.chart-card chart="customers" :title="$c('customers.title')" :sub="$s('customers.sub', ['unit' => $unitLabel])">
                    <x-slot:table>
                        <table class="ui-table">
                            <thead><tr><th class="ui-th">{{ $c('col.period') }}</th><th class="ui-th ui-th--num">{{ $c('customers.new') }}</th><th class="ui-th ui-th--num">{{ $c('customers.returning') }}</th></tr></thead>
                            <tbody>
                                @foreach ($customers['points'] as $i => $p)
                                    <tr><td class="ui-td">{{ $labels[$i] }}</td><td class="ui-td ui-td--num">{{ $p['new'] }}</td><td class="ui-td ui-td--num">{{ $p['returning'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot:table>
                </x-platform.chart-card>

                <x-platform.chart-card chart="dishes" :title="$s('dishes.title')" :sub="$s('dishes.sub')" tall>
                    <x-slot:table>
                        <table class="ui-table">
                            <thead><tr><th class="ui-th">{{ $s('dishes.dish') }}</th><th class="ui-th ui-th--num">{{ $s('dishes.portions') }}</th><th class="ui-th ui-th--num">{{ $s('dishes.value') }}</th></tr></thead>
                            <tbody>
                                @foreach ($dishes as $d)
                                    <tr><td class="ui-td">{{ $d['name'] }}</td><td class="ui-td ui-td--num">{{ number_format($d['quantity']) }}</td><td class="ui-td ui-td--num">{{ $money($d['value']) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot:table>
                </x-platform.chart-card>

                <x-platform.heatmap :heatmap="$heatmap" :title="$c('heatmap.title')" :sub="$c('heatmap.sub')" />

                @if ($table)
                    <x-platform.chart-card chart="restaurants" :title="$s('restaurants.chart')" :sub="$s('restaurants.sub')" wide tall>
                        <x-slot:table>
                            <table class="ui-table">
                                <thead><tr><th class="ui-th">{{ $c('col.restaurant') }}</th><th class="ui-th ui-th--num">{{ $s('kpi.gross') }}</th><th class="ui-th ui-th--num">{{ $c('col.orders') }}</th><th class="ui-th ui-th--num">{{ $c('col.average') }}</th><th class="ui-th ui-th--num">{{ $s('kpi.rating') }}</th><th class="ui-th ui-th--num">{{ $s('restaurants.reviews') }}</th><th class="ui-th ui-th--num">{{ $s('restaurants.delivery') }}</th></tr></thead>
                                <tbody>
                                    @foreach ($table as $r)
                                        <tr>
                                            <td class="ui-td ui-td--strong"><a class="ui-link" href="{{ route('admin.stats.show', ['stat' => $r['id'], ...$period->query()]) }}">{{ $r['name'] }}</a></td>
                                            <td class="ui-td ui-td--num">{{ $money($r['gross_value']) }}</td>
                                            <td class="ui-td ui-td--num">{{ number_format($r['completed_orders']) }}</td>
                                            <td class="ui-td ui-td--num">{{ $money($r['average_order']) }}</td>
                                            <td class="ui-td ui-td--num">{{ $r['rating'] === null ? '–' : number_format($r['rating'], 2) }}</td>
                                            <td class="ui-td ui-td--num">{{ number_format($r['reviews']) }}</td>
                                            <td class="ui-td ui-td--num">{{ $r['delivery_minutes'] === null ? '–' : $c('kpi.deliveryValue', ['minutes' => $r['delivery_minutes']]) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </x-slot:table>
                    </x-platform.chart-card>
                @endif
            </div>

            @if ($fans)
                <section class="plat-panel" aria-labelledby="stats-fans-title">
                    <h2 id="stats-fans-title" class="plat-panel-title">{{ $s('fans.title') }}</h2>
                    <p class="plat-chart-sub">{{ $s('fans.sub') }}</p>
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead><tr><th class="ui-th">{{ $s('fans.customer') }}</th><th class="ui-th ui-th--num">{{ $s('fans.orders') }}</th><th class="ui-th ui-th--num">{{ $s('fans.spent') }}</th></tr></thead>
                            <tbody>
                                @foreach ($fans as $f)
                                    <tr><td class="ui-td">{{ $f['name'] }}</td><td class="ui-td ui-td--num">{{ $f['orders'] }}</td><td class="ui-td ui-td--num">{{ $money($f['spent']) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            <script type="application/json" id="platform-charts">@json($charts)</script>
            @vite('resources/js/jsBack/platform/charts.js')
        @endif
    @endif
</div>
@endsection
