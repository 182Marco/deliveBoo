@extends('layouts.app')

@section('title', __('partner.orders.tabTitle', ['id' => $data->id]))

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.orders.index', $data->restaurant->slug) }}"><x-icon name="arrow-left" size="18" /> {{ __('partner.orders.all') }}</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.orders.order') }} <span class="adm-mono">#{{ $data->id }}</span></p>
            <h1 class="ui-title">{{ $data->customer_name }} {{ $data->customer_lastName }}</h1>
            <p class="ui-muted">{{ __('partner.orders.placed', ['date' => $data->created_at->translatedFormat('j M Y, H:i')]) }}</p>
        </div>
    </div>

    <div class="adm-detail">
        <section class="ui-card ui-card--pad" aria-labelledby="customer-title">
            <h2 id="customer-title" class="ui-subtitle">{{ __('partner.orders.delivery') }}</h2>
            <dl class="adm-facts">
                <div class="adm-fact">
                    <dt class="adm-fact-label">{{ __('partner.common.address') }}</dt>
                    <dd class="adm-fact-value">{{ $data->customer_address }}</dd>
                </div>
                <div class="adm-fact">
                    <dt class="adm-fact-label">{{ __('partner.common.email') }}</dt>
                    <dd class="adm-fact-value">{{ $data->customer_email }}</dd>
                </div>
                <div class="adm-fact">
                    <dt class="adm-fact-label">{{ __('partner.common.phone') }}</dt>
                    <dd class="adm-fact-value">{{ $data->customer_phone }}</dd>
                </div>
            </dl>

            {{-- proof of payment: the id the provider gave to the transaction --}}
            <h2 class="ui-subtitle adm-section-title">{{ __('partner.orders.payment') }}</h2>
            <dl class="adm-facts">
                <div class="adm-fact">
                    <dt class="adm-fact-label">{{ __('partner.common.status') }}</dt>
                    <dd class="adm-fact-value"><span class="ui-badge ui-badge--basil"><x-icon name="check" size="14" /> {{ __('partner.common.paid') }}</span></dd>
                </div>
                <div class="adm-fact">
                    <dt class="adm-fact-label">{{ __('partner.orders.processedBy') }}</dt>
                    <dd class="adm-fact-value">{{ $data->paymentProvider() }}</dd>
                </div>
                <div class="adm-fact">
                    <dt class="adm-fact-label">{{ __('partner.orders.transaction') }}</dt>
                    <dd class="adm-fact-value adm-mono">{{ $data->transaction_id ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="ui-card ui-card--pad" aria-labelledby="items-title">
            <h2 id="items-title" class="ui-subtitle">{{ __('partner.orders.dishes') }}</h2>
            <ul class="adm-lines">
                @foreach ($data->plates as $plate)
                    <li class="adm-line">
                        <span>@if ($plate->pivot->quantity > 1){{ $plate->pivot->quantity }} × @endif{{ $plate->name }}</span>
                        {{-- the price paid, not the current one of the menu --}}
                        <span class="ui-price">€{{ number_format($plate->pivot->quantity * $plate->pivot->unit_price, 2) }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="adm-total">
                <span>{{ __('partner.orders.totalPaid') }}</span>
                <span class="adm-total-amount ui-price">€{{ number_format($data->price, 2) }}</span>
            </p>
        </section>
    </div>
</div>
@endsection
