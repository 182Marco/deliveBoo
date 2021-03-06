@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Session Deleted --}}
    @if (session('deleted'))
        <div class="alert alert-success">
            <strong>{{ session('deleted') }}</strong>
            Restaurant deleted!
        </div>
    @endif

    <h1 class="my-4">Your Restaurants</h1>


    <!--Create-->
    <a class=" mb-5 btn btn-primary" href="{{ route('admin.restaurants.create')}}">Create</a>
    
    <table class="table"> 
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>city</th>
                <th>Address</th>
                <th colspan="3">Buttons</th>
                <th>plates list</th>
                <th>orders</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($auth_restaurants as $auth_restaurant)              
              <tr>
                  
                <td>{{ str_replace("_"," ", $auth_restaurant->name)}}</td>
                <td>{{ $auth_restaurant->phone }}</td>
                <td>{{ $auth_restaurant->city }}</td>
                <td>{{ $auth_restaurant->address }}</td>
                  
                <td> <a class="btn btn-success btn-sm" href="{{ route('admin.restaurants.show', $auth_restaurant->id) }}">SHOW</a> </td>
                <td> <a class="btn btn-warning btn-sm" href="{{ route('admin.restaurants.edit', $auth_restaurant->id)}}">EDIT</a> </td>
                <td><form class="delete-post-form" action="{{ route('admin.restaurants.destroy', $auth_restaurant->id)}}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input class="btn btn-danger btn-sm" type="submit" value="DELETE">
                    </form>
                </td>
                  <td> <a class="btn btn-success btn-sm" href="{{ route('admin.plates.index', $auth_restaurant->slug) }}">PLATES</a> </td>
                  </td>
                  <td> <a class="btn btn-success btn-sm" href="{{ route('admin.orders.index', $auth_restaurant->slug) }}">ORDERS</a> </td>
                  </td>
              </tr>  
            @endforeach
        </tbody>
    </table>
    {{ $auth_restaurants->appends(request()->query())->links() }}
    {{-- message you are logged in --}}
    @include('layouts/logged')
</div>
{{-- confirm box to avoid user clicks by mistake--}}
@include('layouts/deleteConfirm')
@endsection