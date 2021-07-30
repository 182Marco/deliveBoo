@extends('layouts.app')

@section('content')
<div class="container">
    
    <a class="btn btn-warning btn-sm font-weight-bold my-3 mr-4" href="{{ route('admin.restaurants.index', $restaurant_id) }}">BACK TO RESTAURANTS INDEX</a> 
    <a class="btn btn-primary btn-sm font-weight-bold my-3" href="{{ route('admin.stats.show', $restaurant_id) }}">SHOW ME GRAPHS</a> 

    <h2 class="my-3 font-weight-bold">All your orders</h2>

    <table class="table"> 
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer Name</th>
                <th>Customer Last Name</th>
                <th>Customer Address</th>
                <th>Date</th>
                <th>Price</th>
                {{-- <th colspan="3">Buttons</th> --}}
                <th>Buttons</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)              
              <tr>
                  
                <td>{{ $order->id}}</td>
                <td>{{ $order->customer_name }}</td>
                <td>{{ $order->customer_lastName }}</td>
                <td>{{ $order->customer_address }}</td>
                <td>{{ $order->created_at->toDateString() }}</td>
                <td>{{number_format($order->price,2)}} €</td>         
                <td> <a class="btn btn-success btn-sm font-weight-bold" href="{{ route('admin.orders.show', $order->id) }}">SHOW</a> </td>
                </td>
              </tr>  
            @endforeach
        </tbody>
    </table>  
    {{ $orders->links() }}
</div>
@endsection
