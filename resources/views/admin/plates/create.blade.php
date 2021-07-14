@extends('layouts.app')

@section('content')



    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                
                <h1 class="my-4">CREATE NEW PLATE</h1>

                <form action="{{ route('admin.plates.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')


                    {{-- Name --}}
                    <div class="my-4">
                        <label class="form-label" for="name">Name*</label>
                        <input class="form-control @error('name')
                            is-invalid
                        @enderror" type="text" id="name" name="name" value="{{old('name')}}">
                    </div>

                    {{-- description --}}
                    <div class="my-4">
                        <label class="form-label" for="description">description*</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description" cols="30" rows="4">{{old('description')}}</textarea>
                    </div>

                    {{-- ingredients --}}
                    <div class="my-4">
                        <label class="form-label" for="ingredients">ingredients*</label>
                        <input class="form-control @error('ingredients')
                            is-invalid
                        @enderror" type="text" id="ingredients" name="ingredients" value="{{old('ingredients')}}">
                    </div>

                     {{-- price --}}
                    <div class="my-4">
                        <label class="form-label" for="price">price*</label>
                        <input class="form-control @error('price')
                            is-invalid
                        @enderror" type="text" id="price" name="price" value="{{old('price')}}">
                    </div>


                    @error('types')
                    <div>{{ $message }}</div>
                        
                    @enderror
                    {{-- Add plate Image --}}
                    <div class="my-3">
                        <div>
                            <label for="img" class="form-label">Restaurant Image</label>
                        </div>
                        <input type="file" name="img" id="img">
                        @error('img')
                            <div>{{ $message }}</div>
                        @enderror
                    </div>
                     
                    <div class="my-3">
                    <label for="select">Which restaurant offers this plate ?</label>
                        <select class="form-select" id="select" name="restaurant_id">
                            @foreach ($res_of_user as $res)                        
                            <option value="{{$res->id}}">{{$res->name}}</option>
                            @endforeach
                        </select>
                    </div>

    
                        <button class="mt-3 btn btn-primary" type="submit">Update</button>
                </form>
                </div>
            </div>
        </div>
    </div>

    
@endsection