@extends('layouts.app')

@section('content')

<div class="container">
    <div class="ml-5 mt-5">
        <h1> <strong>Restaurant:</strong><em> {{ $restaurant->name }}</em> </h1>

        <div class="my-4 font-weight-bold">
            <a class="btn btn-warning font-weight-bold" href="{{ route('admin.restaurants.edit', $restaurant->id) }}">Edit</a>
            <a class="btn btn-primary ml-3 font-weight-bold" href="{{ route('admin.restaurants.index') }}"> back to Restaurants</a>
            <a class="btn btn-success ml-3 font-weight-bold" href="{{ route('admin.plates.index', $restaurant->id) }}">Plates List</a>
        </div>

        {{-- Restaurant Image --}}
        <div class="mb-5 row">
            @if ($restaurant->img)
                <div class="col-md-6">
                    <img class="img-fluid" src="{{ asset('storage/' . $restaurant->img) }}" alt="{{$restaurant->name}}">
                </div>
            @endif
        </div>

        {{-- add Types --}}
        @if (count($restaurant->types) >0)
            <p class="d-inline-block mb-3"> <strong class="d-inline-block mr-3">Types:</strong></p>
            @foreach ($restaurant->types as $type )
                <span class="badge badge-info p-2"> {{ $type->name }} </span>
            @endforeach
        @endif

        <p class="mb-3"> <strong>Phone:</strong> <em>{{ $restaurant->phone }}</em> </p>
        <p class="mb-3"> <strong>City:</strong> <em>{{ $restaurant->city }}</em> </p>
        <p class="mb-3">  <strong>Address:</strong> <em>{{ $restaurant->address }}</em></p>
    </div>
</div>
@endsection 