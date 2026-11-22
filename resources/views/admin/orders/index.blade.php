@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.restaurants.index') }}"><x-icon name="arrow-left" size="18" /> Your restaurants</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Orders</p>
            <h1 class="ui-title">Paid orders</h1>
            <p class="ui-muted">Newest first.</p>
        </div>
        <a class="ui-btn ui-btn--dark" href="{{ route('admin.stats.show', $restaurant_id) }}"><x-icon name="chart" size="18" /> Statistics</a>
    </div>

    @if ($orders->isEmpty())
        <div class="ui-empty">
            <img class="ui-empty-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="">
            <p class="ui-subtitle">No orders yet</p>
            <p class="ui-muted">Paid orders will show up here as soon as guests check out.</p>
        </div>
    @else
        {{-- kitchen tickets, newest first: who, where, when and how much at a glance --}}
        <ul class="adm-tickets">
            @foreach ($orders as $order)
                <li class="adm-ticket">
                    <div class="adm-ticket-top">
                        <span class="adm-mono">#{{ $order->id }}</span>
                        <span class="adm-ticket-date">{{ $order->created_at->format('j M Y') }}</span>
                    </div>
                    <h2 class="adm-ticket-name">{{ $order->customer_name }} {{ $order->customer_lastName }}</h2>
                    <p class="adm-meta"><x-icon name="pin" size="16" /> {{ $order->customer_address }}</p>
                    <div class="adm-ticket-foot">
                        <span class="adm-ticket-total ui-price">€{{ number_format($order->price, 2) }}</span>
                        <a class="ui-btn ui-btn--outline ui-btn--sm" href="{{ route('admin.orders.show', $order->id) }}">
                            Details <x-icon name="arrow-right" size="16" />
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    {{ $orders->links() }}
</div>
@endsection
