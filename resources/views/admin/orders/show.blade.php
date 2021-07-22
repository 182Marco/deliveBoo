@extends('layouts.app')

@section('content')

<div class="container">
    <h1 class="my-5"> <strong> Order id:</strong>  <em>{{ $order->id }}</em> </h1>
    <p> <strong>Send/sended to:</strong>  <em> {{ $order->customer_name }} {{ $order->customer_lastName}} </em></p>
    <p  class="my-3"> <strong> Address: </strong> <em>{{$order->customer_address }}</em> </p>
    <p> <strong> Customer email:</strong> <em>{{$order->customer_email }}</em> </p>
    <p class="my-3 mb-5"> <strong>Customer phone: </strong> <em>{{$order->customer_phone }}</em> </p>
   
    <a class="btn btn-success btn-lg" href="{{ route('admin.orders.index') }}">BACK TO INDEX</a>
</div>
@endsection 