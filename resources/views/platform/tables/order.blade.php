@extends('layouts.platform')

@php
    $t = fn (string $key, array $r = []) => __("platform.tables.{$key}", $r);
    $money = fn ($v) => '€'.number_format((float) $v, 2);
    $date = fn ($v) => \Carbon\Carbon::parse($v)->isoFormat('D MMM YYYY, HH:mm');
    $badge = ['completed' => 'ui-badge--basil', 'in_progress' => '', 'cancelled' => 'ui-badge--tomato', 'failed' => 'ui-badge--tomato'][$state];
@endphp

@section('title', $t('order.title', ['id' => $order->id]))
@section('heading', $t('order.title', ['id' => $order->id]))
@section('lead', $t('order.placed', ['date' => $date($order->created_at)]))

@section('content')
    <a class="adm-back" href="{{ route('platform.tables', ['section' => 'orders', ...$period->query()]) }}"><x-icon name="arrow-left" size="18" /> {{ $t('back') }}</a>

    <div class="plat-detail">
        <section class="plat-panel">
            <h2 class="plat-panel-title">{{ $t('order.items') }}</h2>
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead><tr><th class="ui-th">{{ $t('order.dish') }}</th><th class="ui-th ui-th--num">{{ $t('order.qty') }}</th><th class="ui-th ui-th--num">{{ $t('order.unitPrice') }}</th><th class="ui-th ui-th--num">{{ $t('order.total') }}</th></tr></thead>
                    <tbody>
                        @foreach ($lines as $line)
                            <tr><td class="ui-td">{{ $line['name'] }}</td><td class="ui-td ui-td--num">{{ $line['quantity'] }}</td><td class="ui-td ui-td--num">{{ $money($line['unit_price']) }}</td><td class="ui-td ui-td--num">{{ $money($line['total']) }}</td></tr>
                        @endforeach
                        <tr><th class="ui-th" scope="row" colspan="3">{{ $t('order.total') }}</th><td class="ui-td ui-td--num ui-td--strong">{{ $money($order->price) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="plat-side">
            <section class="plat-panel">
                <h2 class="plat-panel-title">{{ $t('order.payment') }}</h2>
                <dl class="plat-facts">
                    <dt class="plat-fact-label">{{ $t('col.status') }}</dt>
                    <dd class="plat-fact-value"><span class="ui-badge {{ $badge }}">{{ $t("state.{$state}") }}</span></dd>
                    <dt class="plat-fact-label">{{ $t('col.restaurant') }}</dt>
                    <dd class="plat-fact-value">
                        @if ($order->restaurant_id)
                            <a class="ui-link" href="{{ route('platform.tables.restaurant', ['restaurant' => $order->restaurant_id, ...$period->query()]) }}">{{ $order->restaurant_name }}</a>, {{ $order->restaurant_city }}
                        @else
                            –
                        @endif
                    </dd>
                    <dt class="plat-fact-label">{{ $t('order.provider') }}</dt>
                    <dd class="plat-fact-value">{{ $order->payment_driver ?? '–' }}</dd>
                    <dt class="plat-fact-label">{{ $t('order.transaction') }}</dt>
                    <dd class="plat-fact-value plat-mono">{{ $order->transaction_id ?? '–' }}</dd>
                    @if ($order->payment_error)
                        <dt class="plat-fact-label">{{ $t('state.failed') }}</dt>
                        <dd class="plat-fact-value">{{ $t('order.refused', ['message' => $order->payment_error]) }}</dd>
                    @endif
                    @if ($order->delivered_at)
                        <dt class="plat-fact-label">{{ $t('state.completed') }}</dt>
                        <dd class="plat-fact-value">{{ $t('order.delivered', ['date' => $date($order->delivered_at)]) }}</dd>
                    @endif
                </dl>
            </section>

            <section class="plat-panel">
                <h2 class="plat-panel-title">{{ $t('order.customer') }}</h2>
                <dl class="plat-facts">
                    <dt class="plat-fact-label">{{ $t('col.customer') }}</dt>
                    <dd class="plat-fact-value">{{ $order->customer_name }} {{ $order->customer_lastName }}</dd>
                    <dt class="plat-fact-label">{{ __('partner.auth.email') }}</dt>
                    <dd class="plat-fact-value">{{ $order->customer_email }}</dd>
                    <dt class="plat-fact-label">{{ $t('order.phone') }}</dt>
                    <dd class="plat-fact-value">{{ $order->customer_phone }}</dd>
                    <dt class="plat-fact-label">{{ $t('order.address') }}</dt>
                    <dd class="plat-fact-value">{{ $order->customer_address }}</dd>
                    <dt class="plat-fact-label">{{ $t('col.account') }}</dt>
                    <dd class="plat-fact-value">{{ $account ? $account->name.' '.$account->last_name.' · '.$t('role.'.$account->role) : $t('order.noAccount') }}</dd>
                </dl>
                <a class="ui-link" href="{{ route('platform.tables.customer', ['email' => $order->customer_email, ...$period->query()]) }}">{{ $t('order.allOfCustomer') }}</a>
            </section>
        </div>
    </div>
@endsection
