@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Ristorante: {{ $restaurant->name }}</h1>

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

    <h2>Numero di telefono: {{ $restaurant->phone }}</h2>
    <h2>Città: {{ $restaurant->city }}</h2>
    <h2>Indirizzo: {{ $restaurant->address }}</h2>

</div>
@endsection 