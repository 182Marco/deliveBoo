@extends('layouts.app')

@section('title', 'Order #' . $data->id)

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.orders.index', $data->restaurant->slug) }}"><x-icon name="arrow-left" size="18" /> All orders</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Order <span class="adm-mono">#{{ $data->id }}</span></p>
            <h1 class="ui-title">{{ $data->customer_name }} {{ $data->customer_lastName }}</h1>
            <p class="ui-muted">Placed on {{ $data->created_at->format('j M Y, H:i') }}</p>
        </div>
    </div>

    <div class="adm-detail">
        <section class="ui-card ui-card--pad" aria-labelledby="customer-title">
            <h2 id="customer-title" class="ui-subtitle">Delivery</h2>
            <dl class="adm-facts">
                <div class="adm-fact">
                    <dt class="adm-fact-label">Address</dt>
                    <dd class="adm-fact-value">{{ $data->customer_address }}</dd>
                </div>
                <div class="adm-fact">
                    <dt class="adm-fact-label">Email</dt>
                    <dd class="adm-fact-value">{{ $data->customer_email }}</dd>
                </div>
                <div class="adm-fact">
                    <dt class="adm-fact-label">Phone</dt>
                    <dd class="adm-fact-value">{{ $data->customer_phone }}</dd>
                </div>
            </dl>

            {{-- proof of payment: the id the provider gave to the transaction --}}
            <h2 class="ui-subtitle adm-section-title">Payment</h2>
            <dl class="adm-facts">
                <div class="adm-fact">
                    <dt class="adm-fact-label">Status</dt>
                    <dd class="adm-fact-value"><span class="ui-badge ui-badge--basil"><x-icon name="check" size="14" /> Paid</span></dd>
                </div>
                <div class="adm-fact">
                    <dt class="adm-fact-label">Processed by</dt>
                    <dd class="adm-fact-value">{{ $data->paymentProvider() }}</dd>
                </div>
                <div class="adm-fact">
                    <dt class="adm-fact-label">Transaction ID</dt>
                    <dd class="adm-fact-value adm-mono">{{ $data->transaction_id ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="ui-card ui-card--pad" aria-labelledby="items-title">
            <h2 id="items-title" class="ui-subtitle">Dishes</h2>
            <ul class="adm-lines">
                @foreach ($data->plates as $plate)
                    <li class="adm-line">
                        <span>{{ $plate->name }}</span>
                        <span class="ui-price">€{{ number_format($plate->price, 2) }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="adm-total">
                <span>Total paid</span>
                <span class="adm-total-amount ui-price">€{{ number_format($data->price, 2) }}</span>
            </p>
        </section>
    </div>
</div>
@endsection
