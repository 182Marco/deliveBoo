@extends('layouts.app')

@section('content')

    <div class="container">
        <h1 class="mb-5">CREATE NEW RESTAURANT</h1>

        <div class="row">
            <div class="col-md-8 offset-md-2">


                <form action="{{ route('admin.restaurants.store')}}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')


                {{-- Name --}}
                <div>
                    <label class="form-label" for="name">Name*</label>
                    <input class="form-control @error('name')
                        is-invalid
                    @enderror" type="text" id="name" name="name" value="{{old('name')}}">
                </div>

                {{-- Phone --}}
                <div>
                    <label class="form-label" for="name">Phone*</label>
                    <input class="form-control @error('phone')
                        is-invalid
                    @enderror" type="text" id="phone" name="phone" value="{{old('phone')}}">
                </div>

                {{-- City --}}
                <div>
                    <label class="form-label" for="name">City*</label>
                    <input class="form-control @error('city')
                        is-invalid
                    @enderror" type="text" id="city" name="city" value="{{old('city')}}">
                </div>

                {{-- Address --}}
                <div>
                    <label class="form-label" for="name">Address*</label>
                    <input class="form-control @error('address')
                        is-invalid
                    @enderror" type="text" id="address" name="address" value="{{old('address')}}">
                </div>

                {{--Add Types--}}
                <h4>Types</h4>
                <div class="mb-3">

                    @foreach ($types as $type)
                    <span class="d-inline-block mr-3">
                        <input type="checkbox" name="types[]" id="type {{ $loop->iteration }}" value="{{ $type->id}}"
                        
                        @if (in_array($type->id, old('types', []) ))  {{-- []<- Default value in case there are no tags --}}
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
                    <input type="file" name="img" id="img">
                    @error('img')
                        <div>{{ $message }}</div>
                    @enderror
                </div>

                <button class="mt-5 btn btn-primary" type="submit">Create</button>
                </form>
            </div>
        </div>
    </div>

    
@endsection