@extends('layouts.app')

@section('content')

    <div class="container">
        <h1 class="mb-5">CREATE NEW RESTAURANT</h1>

        <div class="row">
            <div class="col-md-8 offset-md-2">


                <form action="{{ route('admin.restaurants.update', $restaurant->id)}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')


                    {{-- Name --}}
                    <div>
                        <label class="form-label" for="name">Name*</label>
                        <input class="form-control @error('title')
                            is-invalid
                        @enderror" type="text" id="name" name="name" value="{{old('name', $restaurant->name)}}">
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="form-label" for="name">Phone*</label>
                        <input class="form-control @error('phone')
                            is-invalid
                        @enderror" type="text" id="phone" name="phone" value="{{old('phone', $restaurant->phone)}}">
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="form-label" for="name">City*</label>
                        <input class="form-control @error('city')
                            is-invalid
                        @enderror" type="text" id="city" name="city" value="{{old('city', $restaurant->city)}}">
                    </div>

                {{-- Address --}}
                    <div>
                        <label class="form-label" for="name">Address*</label>
                        <input class="form-control @error('address')
                            is-invalid
                        @enderror" type="text" id="address" name="address" value="{{old('address', $restaurant->address)}}">
                    </div>


                    {{-- Add Restaurant Image --}}
                    <div class="mb-3">
                        <div>
                            <label for="img" class="form-label">Restaurant Image</label>
                        </div>
                        <input type="file" name="img" id="img">
                        @error('img')
                            <div>{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="mt-5 btn btn-primary" type="submit">Update</button>
                </form>
            </div>
        </div>
    </div>

    
@endsection