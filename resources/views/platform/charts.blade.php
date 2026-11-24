@extends('layouts.platform')

@section('title', __('platform.tabs.charts'))
@section('heading', __('platform.charts.title'))
@section('lead', __('platform.charts.lead'))

@php
    $c = fn (string $key, array $r = []) => __("platform.charts.{$key}", $r);
    $money = fn ($v) => $v === null ? '–' : '€'.number_format($v, 2);
    $now = $summary['current'];
    $change = $summary['change'];
    $prevFrom = \Carbon\Carbon::parse($summary['previous_period']['from'])->isoFormat('D MMM YYYY');
    $prevTo = \Carbon\Carbon::parse($summary['previous_period']['to'])->isoFormat('D MMM YYYY');
    $vs = $c('vsPrevious', ['from' => $prevFrom, 'to' => $prevTo]);
    $rate = number_format($summary['commission_rate'] * 100, 0).'%';
@endphp

@push('scripts')
    @vite('resources/js/jsBack/platform/charts.js')
@endpush

@section('content')
    <x-platform.filters :period="$period" />

    @if ($now['gross_orders'] + $now['cancelled_orders'] + $now['failed_orders'] === 0)
        <div class="plat-empty">{{ $c('empty') }}</div>
    @else
        <p class="plat-note">{{ $c('changesVs', ['from' => $prevFrom, 'to' => $prevTo]) }}</p>
        <div class="plat-kpis">
            <x-platform.kpi :label="$c('kpi.gross')" :value="$money($now['gross_value'])" :change="$change['gross_value']" :compare="$vs" :hint="$c('kpi.grossHint')" />
            <x-platform.kpi :label="$c('kpi.revenue')" :value="$money($now['revenue'])" :change="$change['revenue']" :compare="$vs" :hint="$c('kpi.revenueHint', ['rate' => $rate])" estimate />
            <x-platform.kpi :label="$c('kpi.completed')" :value="number_format($now['completed_orders'])" :change="$change['completed_orders']" :compare="$vs" :hint="$c('kpi.completedHint')" />
            <x-platform.kpi :label="$c('kpi.average')" :value="$money($now['average_order'])" :change="$change['average_order']" :compare="$vs" :hint="$c('kpi.averageHint')" />
            <x-platform.kpi :label="$c('kpi.customers')" :value="number_format($now['customers'])" :change="$change['customers']" :compare="$vs" :hint="$c('kpi.customersHint')" />
            <x-platform.kpi :label="$c('kpi.cancelled')" :value="number_format($now['cancelled_orders'])" :change="$change['cancelled_orders']" :up-is-good="false" :compare="$vs" :hint="$c('kpi.cancelledHint', ['value' => $money($now['cancelled_value'])])" />
            <x-platform.kpi :label="$c('kpi.failed')" :value="number_format($now['failed_orders'])" :change="$change['failed_orders']" :up-is-good="false" :compare="$vs" :hint="$c('kpi.failedHint', ['value' => $money($now['failed_value'])])" />
            <x-platform.kpi :label="$c('kpi.delivery')" :value="$now['delivery_median_minutes'] === null ? '–' : $c('kpi.deliveryValue', ['minutes' => $now['delivery_median_minutes']])" :hint="$now['delivery_p90_minutes'] === null ? null : $c('kpi.deliveryHint', ['p90' => $now['delivery_p90_minutes']])" />
        </div>

        <div class="plat-charts">
            <x-platform.chart-card chart="sales" :title="$c('sales.title')" :sub="$c('sales.sub', ['unit' => $unitLabel]).($hasPartial ? ' · '.$c('partialNote', ['unit' => $unitLabel]) : '')" wide>
                <x-slot:table>
                    <table class="ui-table">
                        <thead><tr><th class="ui-th">{{ $c('col.period') }}</th><th class="ui-th ui-th--num">{{ $c('col.gross') }}</th><th class="ui-th ui-th--num">{{ $c('col.orders') }}</th><th class="ui-th ui-th--num">{{ $c('col.average') }}</th><th class="ui-th ui-th--num">{{ $c('col.revenue') }}</th></tr></thead>
                        <tbody>
                            @foreach ($series as $i => $p)
                                <tr><td class="ui-td">{{ $labels[$i] }}</td><td class="ui-td ui-td--num">{{ $money($p['gross_value']) }}</td><td class="ui-td ui-td--num">{{ $p['completed_orders'] }}</td><td class="ui-td ui-td--num">{{ $money($p['average_order']) }}</td><td class="ui-td ui-td--num">{{ $money($p['revenue']) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:table>
            </x-platform.chart-card>

            <x-platform.chart-card chart="orders" :title="$c('orders.title')" :sub="$c('orders.sub', ['unit' => $unitLabel])" />
            <x-platform.chart-card chart="average" :title="$c('average.title')" :sub="$c('average.sub', ['unit' => $unitLabel])" />

            <x-platform.chart-card chart="compare" :title="$c('compare.title')" :sub="$c('compare.sub', ['unit' => $unitLabel, 'from' => \Carbon\Carbon::parse($summary['previous_period']['from'])->isoFormat('D MMM YYYY'), 'to' => \Carbon\Carbon::parse($summary['previous_period']['to'])->isoFormat('D MMM YYYY')])" wide />

            <x-platform.heatmap :heatmap="$heatmap" :title="$c('heatmap.title')" :sub="$c('heatmap.sub')" />

            <x-platform.chart-card chart="top" :title="$c('top.title')" :sub="$c('top.sub')" tall>
                <x-slot:table>
                    <table class="ui-table">
                        <thead><tr><th class="ui-th">{{ $c('col.restaurant') }}</th><th class="ui-th">{{ $c('col.city') }}</th><th class="ui-th ui-th--num">{{ $c('col.gross') }}</th><th class="ui-th ui-th--num">{{ $c('col.orders') }}</th><th class="ui-th ui-th--num">{{ $c('col.rating') }}</th></tr></thead>
                        <tbody>
                            @foreach ($top as $r)
                                <tr><td class="ui-td">{{ $r['name'] }}</td><td class="ui-td">{{ $r['city'] }}</td><td class="ui-td ui-td--num">{{ $money($r['gross_value']) }}</td><td class="ui-td ui-td--num">{{ $r['completed_orders'] }}</td><td class="ui-td ui-td--num">{{ $r['rating'] ?? '–' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:table>
            </x-platform.chart-card>

            <x-platform.chart-card chart="cities" :title="$c('cities.title')" :sub="$c('cities.sub')" tall>
                <x-slot:table>
                    <table class="ui-table">
                        <thead><tr><th class="ui-th">{{ $c('col.city') }}</th><th class="ui-th ui-th--num">{{ $c('col.gross') }}</th><th class="ui-th ui-th--num">{{ $c('col.orders') }}</th><th class="ui-th ui-th--num">{{ $c('col.average') }}</th></tr></thead>
                        <tbody>
                            @foreach ($cities as $z)
                                <tr><td class="ui-td">{{ $z['zone'] }}</td><td class="ui-td ui-td--num">{{ $money($z['gross_value']) }}</td><td class="ui-td ui-td--num">{{ $z['completed_orders'] }}</td><td class="ui-td ui-td--num">{{ $money($z['average_order']) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:table>
            </x-platform.chart-card>

            <x-platform.chart-card chart="cuisines" :title="$c('cuisines.title')" :sub="$c('cuisines.sub')" tall>
                <x-slot:table>
                    <table class="ui-table">
                        <thead><tr><th class="ui-th">{{ $c('col.cuisine') }}</th><th class="ui-th ui-th--num">{{ $c('col.gross') }}</th><th class="ui-th ui-th--num">{{ $c('col.orders') }}</th></tr></thead>
                        <tbody>
                            @foreach ($cuisines as $k)
                                <tr><td class="ui-td">{{ $k['name'] }}</td><td class="ui-td ui-td--num">{{ $money($k['gross_value']) }}</td><td class="ui-td ui-td--num">{{ $k['completed_orders'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:table>
            </x-platform.chart-card>

            <x-platform.chart-card chart="customers" :title="$c('customers.title')" :sub="$c('customers.sub', ['unit' => $unitLabel])">
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

            <x-platform.chart-card chart="distribution" :title="$c('distribution.title')" :sub="$c('distribution.sub', ['rate' => $customers['repeat_rate'] === null ? '–' : number_format($customers['repeat_rate'] * 100, 1).'%'])">
                <x-slot:table>
                    <table class="ui-table">
                        <thead><tr><th class="ui-th">{{ $c('col.orderCount') }}</th><th class="ui-th ui-th--num">{{ $c('col.customers') }}</th></tr></thead>
                        <tbody>
                            @foreach ($customers['distribution'] as $band => $n)
                                <tr><td class="ui-td">{{ $band }}</td><td class="ui-td ui-td--num">{{ $n }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:table>
            </x-platform.chart-card>

            @if ($hasRegistrations)
                <x-platform.chart-card chart="registrations" :title="$c('registrations.title')" :sub="$c('registrations.sub', ['unit' => $unitLabel])" />
            @else
                <section class="plat-chart">
                    <h2 class="plat-panel-title">{{ $c('registrations.title') }}</h2>
                    <p class="plat-note">{{ $c('registrations.none') }}</p>
                </section>
            @endif
        </div>

        <script type="application/json" id="platform-charts">@json($charts)</script>
    @endif
@endsection
