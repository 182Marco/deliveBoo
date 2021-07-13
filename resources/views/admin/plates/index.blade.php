@extends('layouts.app')

@section('content')
<div class="container">
    here you will see all plates that belongs to the restaurant from whitch user has reach this view
    {{ $plate }}
</div>
{{-- confirm box to avoid user clicks by mistake--}}
@include('layouts/deleteConfirm')
@endsection