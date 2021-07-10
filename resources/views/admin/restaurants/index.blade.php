@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Your Restaurant</h1>
    
    <table class="table"> 
        <thead>
            <tr>
                <th>Restaurant Name</th>
                <th>Restaurant Phone</th>
                <th>Restaurant city</th>
                <th>Restaurant Address</th>
                <th colspan="3">Buttons</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($restaurants as $restaurant)              
              <tr>
                  
                  <td>{{ str_replace("_"," ", $restaurant->name)}}</td>
                  <td>{{ $restaurant->phone }}</td>
                  <td>{{ $restaurant->city }}</td>
                  <td>{{ $restaurant->address }}</td>
                  
                  <td> <a class="btn btn-success btn-sm" href="#">SHOW</a> </td>
                  <td> <a class="btn btn-warning btn-sm" href="#">EDIT</a> </td>
                  <td> <a class="btn btn-danger btn-sm" href="#">DELETE</a> </td>
                  </td>
              </tr>  
            @endforeach
        </tbody>
    </table>
</div>
@endsection