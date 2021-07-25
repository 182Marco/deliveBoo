@extends('layouts.app')

@section('content')
{{-- confirm box to avoid user clicks by mistake--}}
<section class="container">
    {{-- <h1>{{$related_orders}}</h1> --}}
        <a class="btn btn-warning btn-sm font-weight-bold my-3 mr-4" href="{{ route('admin.orders.index', $restaurant_id) }}">BACK TO ORDERS INDEX</a> 
        <canvas id="expensessChart"></canvas>
        <h3 class="my-4 text-center">The average expense: <span id="txtAverageEl"></span> € </h3>
</section>

{{-- passing json to js --}}
<script type="text/javascript">const relatedOrders = {!! json_encode($related_orders) !!};</script>
 {{-- the releted script --}}
 <script type='text/javascript' src="{{asset('js/chart.js')}}"></script>
@endsection 