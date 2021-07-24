@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Session Deleted --}}
    @if (session('deleted'))
        <div class="alert alert-success">
            <strong>{{ session('deleted') }}</strong>
            Order deleted!
        </div>
    @endif

    <h2 class="my-3 font-weight-bold">All your orders</h2>

    {{-- <!--Create-->
    <a class=" mb-5 btn btn-primary font-weight-bold" href="{{ route('admin.orders.create', $restaurant_id)}}">Create</a> --}}
    
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
                  
                <td>{{ str_replace("_"," ", $order->id)}}</td>
                <td>{{ $order->customer_name }}</td>
                <td>{{ $order->customer_lastName }}</td>
                <td>{{ $order->customer_address }}</td>
                <td>{{ $order->created_at->toDateString() }}</td>
                <td>{{ $order->price }} €</td>
                  
                <td> <a class="btn btn-success btn-sm font-weight-bold" href="{{ route('admin.orders.show', $order->id, $restaurant_id) }}">SHOW</a> </td>
                {{-- <td> <a class="btn btn-warning btn-sm font-weight-bold" href="{{ route('admin.plates.edit', $order->id)}}">EDIT</a> </td>
                <td><form class="delete-post-form font-weight-bold" action="{{ route('admin.plates.destroy', $order->id )}}" method="POST"> --}}
                    {{-- @csrf
                    @method('DELETE')
                    <input class="btn btn-danger btn-sm" type="submit" value="DELETE">
                    </form> --}}
                </td>
              </tr>  
            @endforeach
        </tbody>
    </table>
</div>
{{-- confirm box to avoid user clicks by mistake--}}
@include('layouts/deleteConfirm')
@endsection

