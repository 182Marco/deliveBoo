@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Restaurants</h1>
    
    <table class="table"> 
        <thead>
            <tr>
                <th colspan="3">Restaurant Name</th>
                <th colspan="2">Restaurant Phone</th>
                <th colspan="2">Restaurant city</th>
                <th colspan="3">Restaurant Address</th>
                <th colspan="3">Buttons</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($restaurants as $restaurant)              
              <tr>
                  
                  <td colspan="3">{{ str_replace("_"," ", $restaurant->name)}}</td>
                  <td colspan="2">{{ $restaurant->phone }}</td>
                  <td colspan="2">{{ $restaurant->city }}</td>
                  <td colspan="3">{{ $restaurant->address }}</td>
                  
                  <td >SHOW</td>
                  <td >EDIT</td>
                  <td >DELETE</td>
                  </td>
              </tr>  
            @endforeach
        </tbody>
    </table>
</div>
@endsection