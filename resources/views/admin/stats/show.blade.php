@extends('layouts.app')

@section('content')
{{-- confirm box to avoid user clicks by mistake--}}
<section class="container">
    {{-- <h1>{{$related_orders}}</h1> --}}
        <a class="btn btn-warning btn-sm font-weight-bold my-3 mr-4" href="{{ route('admin.orders.index', $restaurant_id) }}">BACK TO ORDERS INDEX</a> 
        <canvas id="expensessChart"></canvas>
        <p class="mt-5">The average expense: <span id="txtAverageEl"></span></p>
        <p class="mb-5">The gratest company fan is: <span id="biggestFan"></span>, who bought: <span id="purchaseTimes"></span> times and spent <span id="biggestFanExpense"></span> in total</p>
        <canvas id="fansChart"></canvas>
        <canvas id="incomeByMonthPoints" class="mt-5"></canvas>
        <p class="mt-5">Current month income is <span id="monthIncomeEL"></span></p>
        <p >Current year income is <span id="monthYearEL"></span></p>
        <p >Current month income rapresents <span id="incomePercEl"></span> of annual income  </p> 
        <canvas id="incomeMonth" ></canvas>
</section>

{{-- passing json to js --}}
<script type="text/javascript">const relatedOrders = {!! json_encode($related_orders) !!};</script>
 {{-- the releted script --}}
 <script type='text/javascript' src="{{asset('js/chart.js')}}"></script>
@endsection 


<style>
    p{
        font-size: 1.4rem
    }
    p > span{
        color: #5b6acd
    }
</style>