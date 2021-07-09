@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Restaurants</h1>
    
    <table>
        <thead>
            <tr>
                <th>Restaurant Name</th>
                <th>Restaurant Phone</th>
                <th>Restaurant Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($restaurants as $restaurant)              
              <tr>
                  <td>{{ $restaurant->restaurant_name }}</td>
                  <td>{{ $restaurant->restaurant_phone }}</td>
                  <td>{{ $restaurant->restaurant_address }}</td>
                  <td>SHOW</td>
                  <td>EDIT</td>
                  <td>DELETE</td>
                  </td>
              </tr>  
            @endforeach
        </tbody>
    </table>
</div>
@endsection