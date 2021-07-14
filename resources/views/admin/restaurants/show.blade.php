@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Restaurant: {{ $restaurant->name }}</h1>

    <div class="mb-5">
        <a class="btn btn-warning" href="{{ route('admin.restaurants.edit', $restaurant->id) }}">Edit</a>
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
        <h4>Types</h4>
        @foreach ($restaurant->types as $type )
            <span class="badge badge-primary"> {{ $type->name }} </span>
        @endforeach
    @endif

    <h2>Phone: {{ $restaurant->phone }}</h2>
    <h2>City: {{ $restaurant->city }}</h2>
    <h2>Address: {{ $restaurant->address }}</h2>

</div>
@endsection 