@extends('layouts.app')

@section('content')

<div class="container">
    <h1 class="mt-4">Piatto: {{ $plate->name }}</h1>

    <div class="my-5">
        <a class="btn btn-warning" href="{{ route('admin.plates.edit', $plate->id) }}">Edit</a>
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

</div>
@endsection 