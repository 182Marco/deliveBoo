@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Session Deleted --}}
    @if (session('deleted'))
        <div class="alert alert-success">
            <strong>{{ session('deleted') }}</strong>
            Plate deleted!
        </div>
    @endif

    <h2 class="my-3 font-weight-bold">All your plates</h2>

    <!--Create-->
    <a class=" mb-5 btn btn-primary font-weight-bold" href="{{ route('admin.plates.create', $restaurant_id)}}">Create</a>
    
    <table class="table"> 
        <thead>
            <tr>
                <th>Name</th>
                <th>ingredients</th>
                <th>description</th>
                <th>available</th>
                <th>price</th>
                <th colspan="3">Buttons</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($res_plates as $res_plate)              
              <tr>
                  
                <td>{{ str_replace("_"," ", $res_plate->name)}}</td>
                <td>{{ $res_plate->ingredients }}</td>
                <td>{{ $res_plate->description }}</td>
                @if($res_plate->visible == 1)<td> yes </td> @else <td> no </td> @endif
                <td>{{ $res_plate->price }} €</td>
                  
                <td> <a class="btn btn-success btn-sm font-weight-bold" href="{{ route('admin.plates.show', $res_plate->id) }}">SHOW</a> </td>
                <td> <a class="btn btn-warning btn-sm font-weight-bold" href="{{ route('admin.plates.edit', $res_plate->id)}}">EDIT</a> </td>
                <td><form class="delete-post-form font-weight-bold" action="{{ route('admin.plates.destroy', $res_plate->id )}}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input class="btn btn-danger btn-sm" type="submit" value="DELETE">
                    </form>
                </td>
              </tr>  
            @endforeach
        </tbody>
    </table>
</div>
{{-- confirm box to avoid user clicks by mistake--}}
@include('layouts/deleteConfirm')
@endsection