@extends('layouts.app')

@section('content')

<div class="container">
    <h2 class="mt-3 font-weight-bold">Piatto: {{ $plate->name }}</h2>

    <div class="my-5">
        <a class="btn btn-warning font-weight-bold" href="{{ route('admin.plates.edit', $plate->slug) }}">Edit</a>
        <a class="btn btn-primary font-weight-bold ml-3" href="{{ route('admin.restaurants.index') }}"> back to Restaurants</a>
        <a class="btn btn-primary font-weight-bold ml-3" href="{{ route('admin.plates.index', $plate->restaurant_id) }}">Plates List</a>
    </div>

    {{-- plate Image --}}
    <div class="mb-5 row">
        @if ($plate->img)
            <div class="col-md-6">
                <img class="img-fluid" src="{{ asset('storage/' . $plate->img) }}" alt="{{$plate->name}}">
            </div>
        @endif
    </div>


    <p> <strong>Description: </strong><em> {{ $plate->description }}</em></p>
    <p> <strong>Ingredients:</strong><em> {{ $plate->ingredients }}</em></p>
    <p> <strong>Price:</strong><em> {{ number_format($plate->price,2)}} €</em></p>
    <p> <strong>Now available: </strong> <em>@if($plate->visible == 1) yes @else no @endif </em> </p>

    <form class="delete-post-form font-weight-bold" action="{{ route('admin.plate.delete', $plate->id )}}" method="POST">
        @csrf
        @method('DELETE')
        <input class="btn btn-danger btn-sm" type="submit" value="DELETE">
    </form>

</div>
{{-- confirm box to avoid user clicks by mistake--}}
@include('layouts/deleteConfirm')
@endsection 