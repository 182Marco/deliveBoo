@extends('layouts.platform')

@php
    $t = fn (string $key, array $r = []) => __("platform.tables.{$key}", $r);
    $c = fn (string $key, array $r = []) => __("platform.charts.{$key}", $r);
    $money = fn ($v) => $v === null ? '–' : '€'.number_format($v, 2);
    $day = fn ($v) => \Carbon\Carbon::parse($v)->isoFormat('D MMM YYYY');
    $badge = ['completed' => 'ui-badge--basil', 'in_progress' => '', 'cancelled' => 'ui-badge--tomato', 'failed' => 'ui-badge--tomato'];
@endphp

@section('title', $t('customer.title'))
@section('heading', $name !== '' ? $name : $email)
@section('lead', $email.' · '.$t('customer.since', ['date' => $day($first), 'last' => $day($last)]))

@section('content')
    <a class="adm-back" href="{{ route('platform.tables', ['section' => 'customers', ...$period->query()]) }}"><x-icon name="arrow-left" size="18" /> {{ $t('back') }}</a>
    <x-platform.filters :period="$period" :keep="['email' => $email]" />

    <p class="plat-note">
        {{ $account ? $t('customer.accountOf', ['role' => $t('role.'.$account->role), 'date' => $day($account->created_at)]) : $t('order.noAccount') }}
    </p>

    <div class="plat-kpis">
        <x-platform.kpi :label="$c('kpi.gross').' · '.$t('restaurant.inPeriod')" :value="$money($totals['gross_value'])" />
        <x-platform.kpi :label="$c('kpi.completed').' · '.$t('restaurant.inPeriod')" :value="number_format($totals['completed_orders'])" />
        <x-platform.kpi :label="$c('kpi.gross').' · '.$t('customer.lifetime')" :value="$money($lifetime['gross_value'])" />
        <x-platform.kpi :label="$c('kpi.average').' · '.$t('customer.lifetime')" :value="$money($lifetime['average_order'])" />
    </div>

    <section class="plat-panel">
        <h2 class="plat-panel-title">{{ $t('customer.orders') }}</h2>
        @if ($orders->total() === 0)
            <p class="plat-note">{{ $t('none') }}</p>
        @else
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead><tr><th class="ui-th">{{ $t('col.order') }}</th><th class="ui-th">{{ $t('col.date') }}</th><th class="ui-th">{{ $t('col.restaurant') }}</th><th class="ui-th">{{ $t('col.status') }}</th><th class="ui-th ui-th--num">{{ $t('col.amount') }}</th></tr></thead>
                    <tbody>
                        @foreach ($orders as $o)
                            @php($st = \App\Analytics\Tables::stateOf($o))
                            <tr>
                                <td class="ui-td ui-td--strong"><a class="ui-link" href="{{ route('platform.tables.order', ['order' => $o->id, ...$period->query()]) }}">#{{ $o->id }}</a></td>
                                <td class="ui-td">{{ \Carbon\Carbon::parse($o->created_at)->isoFormat('D MMM YYYY, HH:mm') }}</td>
                                <td class="ui-td">{{ $o->restaurant_name ?? '–' }}</td>
                                <td class="ui-td"><span class="ui-badge {{ $badge[$st] }}">{{ $t("state.{$st}") }}</span></td>
                                <td class="ui-td ui-td--num">{{ $money((float) $o->price) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $orders->links() }}
        @endif
    </section>
@endsection
