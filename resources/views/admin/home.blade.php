@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p class="mb-3">{{ __('Successfully logged in!') }}</p>  
                    <span >Click here to see you're restaurants</span>
                    <a href="{{ route('admin.restaurants.index') }}" class="btn btn-success ml-3">my restaurants</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
