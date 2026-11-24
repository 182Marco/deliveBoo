@extends('layouts.app')

@section('title', __('partner.common.orders'))

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.restaurants.index') }}"><x-icon name="arrow-left" size="18" /> {{ __('partner.common.yourRestaurants') }}</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.common.orders') }}</p>
            <h1 class="ui-title">{{ __('partner.orders.heading') }}</h1>
            <p class="ui-muted">{{ __('partner.orders.lead') }}</p>
        </div>
        <a class="ui-btn ui-btn--dark" href="{{ route('admin.stats.show', $restaurant_id) }}"><x-icon name="chart" size="18" /> {{ __('partner.common.statistics') }}</a>
    </div>

    @if ($orders->isEmpty())
        <div class="ui-empty">
            <img class="ui-empty-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="">
            <p class="ui-subtitle">{{ __('partner.orders.emptyTitle') }}</p>
            <p class="ui-muted">{{ __('partner.orders.emptyText') }}</p>
        </div>
    @else
        {{-- one slim row per order, newest first: id, date, customer, address, total --}}
        <ul class="adm-orders">
            @foreach ($orders as $order)
                <li class="adm-orders-item">
                    <a class="adm-order-row" href="{{ route('admin.orders.show', $order->id) }}" aria-label="{{ __('partner.orders.details') }} #{{ $order->id }}">
                        <span class="adm-order-id adm-mono">#{{ $order->id }}</span>
                        <span class="adm-order-date">{{ $order->created_at->translatedFormat('j M Y') }}</span>
                        <span class="adm-order-name">{{ $order->customer_name }} {{ $order->customer_lastName }}</span>
                        <span class="adm-order-address">{{ $order->customer_address }}</span>
                        <span class="adm-order-total ui-price">€{{ number_format($order->price, 2) }}</span>
                        <span class="adm-order-go"><x-icon name="arrow-right" size="16" /></span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="adm-orders-pager">{{ $orders->links() }}</div>
</div>
@endsection
