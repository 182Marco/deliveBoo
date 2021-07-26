@extends('layouts.app')

@section('content')

<div class="container">
  <div class="row">
     <div class="col-md-5 offset-md-1">
         <h1 class="my-5"> <strong> Order id:</strong>  <em>{{ $data->id }}</em> </h1>
         <p> <strong>Send/sended to:</strong>  <em> {{ $data->customer_name }} {{ $data->customer_lastName}} </em></p>
         <p  class="my-3"> <strong> Address: </strong> <em>{{$data->customer_address }}</em> </p>
         <p> <strong> Customer email:</strong> <em>{{$data->customer_email }}</em> </p>
         <p class="my-3"> <strong>Customer phone: </strong> <em>{{$data->customer_phone }}</em> </p>
         <h3 class="my-3 my-5"> <strong>Total price: </strong> <em>{{$data->price }} €</em> </h3>
         <a class="btn btn-success btn-lg" href="{{ route('admin.orders.index', $data->plates[0]->restaurant_id) }}">BACK TO INDEX</a>
    </div>
     <div class="col-md-5">
         <h2 class="my-5"> <strong>plates:</strong></h2>
         <article>
            @foreach ($data->plates as $plate)
            <h3>{{$plate->name}}</h3>
            {{-- <small><strong>description: </strong> {{$plate->description}}</small>
            <br>
            <small><strong>ingredients: </strong>{{$plate->ingredients}}</small> --}}
            <p> <strong>price: </strong> <em>{{$plate->price}} €</em></p> 
         @endforeach
        </article>    
    </div>
  </div>
</div>
@endsection 