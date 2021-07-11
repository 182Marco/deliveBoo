@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Your Restaurants</h1>
    
    <table class="table"> 
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>city</th>
                <th>Address</th>
                <th colspan="3">Buttons</th>
                <th>plates list</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($auth_restaurants as $auth_restaurant)              
              <tr>
                  
                  <td>{{ str_replace("_"," ", $auth_restaurant->name)}}</td>
                  <td>{{ $auth_restaurant->phone }}</td>
                  <td>{{ $auth_restaurant->city }}</td>
                  <td>{{ $auth_restaurant->address }}</td>
                  
                  <td> <a class="btn btn-success btn-sm" href="#">SHOW</a> </td>
                  <td> <a class="btn btn-warning btn-sm" href="#">EDIT</a> </td>
                  <td> <a class="btn btn-danger btn-sm" href="#">DELETE</a> </td>
                  <td> <a class="btn btn-success btn-sm" href="#">PLATES</a> </td>
                  </td>
              </tr>  
            @endforeach
        </tbody>
    </table>
</div>
@endsection