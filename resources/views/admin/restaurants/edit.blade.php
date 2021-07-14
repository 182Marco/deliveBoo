@extends('layouts.app')

@section('content')

    <div class="container">
        <h1 class="mb-5">EDIT RESTAURANT</h1>

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

                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="form-label" for="phone">Phone*</label>
                        <input class="form-control @error('phone')
                            is-invalid
                        @enderror" type="text" id="phone" name="phone" value="{{old('phone', $restaurant->phone)}}">

                        @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="form-label" for="city">City*</label>
                        <input class="form-control @error('city')
                            is-invalid
                        @enderror" type="text" id="city" name="city" value="{{old('city', $restaurant->city)}}">

                        @error('city')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                {{-- Address --}}
                    <div>
                        <label class="form-label" for="address">Address*</label>
                        <input class="form-control @error('address')
                            is-invalid
                        @enderror" type="text" id="address" name="address" value="{{old('address', $restaurant->address)}}">

                        @error('address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                {{--Add Types--}}
                <h4>Types</h4>
                <div class="mb-3">

                    @foreach ($types as $type)
                    <span class="d-inline-block mr-3">
                        <input type="checkbox" name="types[]" id="type {{ $loop->iteration }}" value="{{ $type->id}}"
                        
                        @if ($errors->any() && in_array($type->id, old('types'))) 
                            checked
                        @elseif (! $errors->any() && $restaurant->types->contains($type->id))
                            checked    
                        @endif>

                        <label for="type {{ $loop->iteration }}"> {{ $type->name }} </label>
                    </span>
                    @endforeach

                    @error('types')
                    <div>{{ $message }}</div>
                        
                    @enderror
                </div>

                    {{-- Add Restaurant Image --}}
                    <div class="mb-3">
                        <div>
                            <label for="img" class="form-label">Restaurant Image</label>
                        </div>

                     @if ($restaurant->img)
                        <div class="mb-3">
                            <img width="200" src="{{ asset('storage/' . $restaurant->img) }}" alt="{{ $restaurant->name }}">
                        </div>
                    @endif

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