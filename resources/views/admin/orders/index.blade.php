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
        {{-- kitchen tickets, newest first: who, where, when and how much at a glance --}}
        <ul class="adm-tickets">
            @foreach ($orders as $order)
                <li class="adm-ticket">
                    <div class="adm-ticket-top">
                        <span class="adm-mono">#{{ $order->id }}</span>
                        <span class="adm-ticket-date">{{ $order->created_at->translatedFormat('j M Y') }}</span>
                    </div>
                    <h2 class="adm-ticket-name">{{ $order->customer_name }} {{ $order->customer_lastName }}</h2>
                    <p class="adm-meta"><x-icon name="pin" size="16" /> {{ $order->customer_address }}</p>
                    <div class="adm-ticket-foot">
                        <span class="adm-ticket-total ui-price">€{{ number_format($order->price, 2) }}</span>
                        <a class="ui-btn ui-btn--outline ui-btn--sm" href="{{ route('admin.orders.show', $order->id) }}">
                            {{ __('partner.orders.details') }} <x-icon name="arrow-right" size="16" />
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    {{ $orders->links() }}
</div>
@endsection
