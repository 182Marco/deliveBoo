@extends('layouts.app')

@section('title', 'Statistics')

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ url()->previous() }}"><x-icon name="arrow-left" size="18" /> Back</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Statistics</p>
            <h1 class="ui-title">How your kitchen is doing</h1>
            <p class="ui-muted">Computed from paid orders only.</p>
        </div>
    </div>

    {{-- the values are written by resources/js/jsBack/chart.js --}}
    <div class="adm-kpis">
        <div class="adm-kpi">
            <span class="adm-kpi-label">Average order</span>
            <span class="adm-kpi-value" id="txtAverageEl">–</span>
        </div>
        <div class="adm-kpi">
            <span class="adm-kpi-label">This month</span>
            <span class="adm-kpi-value" id="monthIncomeEL">–</span>
        </div>
        <div class="adm-kpi">
            <span class="adm-kpi-label">This year</span>
            <span class="adm-kpi-value" id="monthYearEL">–</span>
        </div>
        <div class="adm-kpi">
            <span class="adm-kpi-label">Month share of year</span>
            <span class="adm-kpi-value" id="incomePercEl">–</span>
        </div>
    </div>

    <div class="ui-notice adm-flash">
        <x-icon name="user" />
        <p>Your most loyal guest is <strong id="biggestFan">–</strong>: <strong id="purchaseTimes">–</strong> orders, <strong id="biggestFanExpense">–</strong> spent in total.</p>
    </div>

    <div class="adm-charts">
        <div class="adm-chart"><canvas id="expensessChart"></canvas></div>
        <div class="adm-chart"><canvas id="fansChart"></canvas></div>
        <div class="adm-chart"><canvas id="incomeByMonthPoints"></canvas></div>
        <div class="adm-chart"><canvas id="incomeMonth"></canvas></div>
    </div>
</div>

{{-- passing json to js --}}
<script>const relatedOrders = {!! json_encode($related_orders) !!};</script>
@vite('resources/js/jsBack/chart.js')
@endsection
