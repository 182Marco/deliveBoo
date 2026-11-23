@extends('layouts.app')

@section('title', __('partner.common.statistics'))

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ url()->previous() }}"><x-icon name="arrow-left" size="18" /> {{ __('partner.common.back') }}</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.common.statistics') }}</p>
            <h1 class="ui-title">{{ __('partner.stats.heading') }}</h1>
            <p class="ui-muted">{{ __('partner.stats.lead') }}</p>
        </div>
    </div>

    {{-- the values are written by resources/js/jsBack/chart.js --}}
    <div class="adm-kpis">
        <div class="adm-kpi">
            <span class="adm-kpi-label">{{ __('partner.stats.average') }}</span>
            <span class="adm-kpi-value" id="txtAverageEl">–</span>
        </div>
        <div class="adm-kpi">
            <span class="adm-kpi-label">{{ __('partner.stats.thisMonth') }}</span>
            <span class="adm-kpi-value" id="monthIncomeEL">–</span>
        </div>
        <div class="adm-kpi">
            <span class="adm-kpi-label">{{ __('partner.stats.thisYear') }}</span>
            <span class="adm-kpi-value" id="monthYearEL">–</span>
        </div>
        <div class="adm-kpi">
            <span class="adm-kpi-label">{{ __('partner.stats.share') }}</span>
            <span class="adm-kpi-value" id="incomePercEl">–</span>
        </div>
    </div>

    <div class="ui-notice adm-flash">
        <x-icon name="user" />
        <p>{!! __('partner.stats.fan', ['name' => '<strong id="biggestFan">–</strong>', 'times' => '<strong id="purchaseTimes">–</strong>', 'spent' => '<strong id="biggestFanExpense">–</strong>']) !!}</p>
    </div>

    <div class="adm-charts">
        <div class="adm-chart"><canvas id="expensessChart"></canvas></div>
        <div class="adm-chart"><canvas id="fansChart"></canvas></div>
        <div class="adm-chart"><canvas id="incomeByMonthPoints"></canvas></div>
        <div class="adm-chart"><canvas id="incomeMonth"></canvas></div>
    </div>
</div>

{{-- passing json to js: the orders and the chart texts in the current language --}}
<script>const relatedOrders = {!! json_encode($related_orders) !!}; const chartText = @json(__('partner.stats.chart'));</script>
@vite('resources/js/jsBack/chart.js')
@endsection
