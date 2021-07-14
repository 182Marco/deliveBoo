@extends('layouts.app')

@section('content')



    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                
                <h2 class="mt-2 mb-3 font-weight-bold">CREATE NEW PLATE</h2>

                <form action="{{ route('admin.plates.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')


                    {{-- Name --}}
                    <div class="my-2">
                        <label class="form-label" for="name">Name*</label>
                        <input class="form-control @error('name')
                            is-invalid
                        @enderror" type="text" id="name" name="name" value="{{old('name')}}">
                    </div>

                    {{-- description --}}
                    <div class="my-2">
                        <label class="form-label" for="description">description*</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description" cols="30" rows="4">{{old('description')}}</textarea>
                    </div>

                    {{-- ingredients --}}
                    <div class="my-2">
                        <label class="form-label" for="ingredients">ingredients*</label>
                        <input class="form-control @error('ingredients')
                            is-invalid
                        @enderror" type="text" id="ingredients" name="ingredients" value="{{old('ingredients')}}">
                    </div>

                     {{-- price --}}
                    <div class="my-2">
                        <label class="form-label" for="price">price*</label>
                        <input class="form-control @error('price')
                            is-invalid
                        @enderror" type="text" id="price" name="price" value="{{old('price')}}">
                    </div>

                    {{-- Is visible --}}
                    <div class="my-3">
                        <p class="font-weight-bold my-0 d-inline-block mr-2">Available now?</p>

                        <label id="yes_visible">yes</label>
                        <input type="radio" name="visible" id="yes_visible" value="1" @if(old('visible')) checked  @endif>

                        <label id="no_visible">no</label>
                        <input type="radio" name="visible" id="no_visible" value="0" @if(!old('visible')) checked  @endif>
                    </div>


                    @error('types')
                    <div>{{ $message }}</div>
                        
                    @enderror
                    {{-- Add plate Image --}}
                    <div class="my-2">
                            <label for="img" class="form-label font-weight-bold">Plate Image: </label>
                        <input class="d-inline-block ml-2" type="file" name="img" id="img">
                        @error('img')
                            <div>{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="my-3 form-group">
                    <label for="select">Which restaurant offers this plate ?</label>
                        <select class="form-control" id="select" name="restaurant_id">
                            @foreach ($res_of_user as $res)                        
                            <option value="{{$res->id}}">{{$res->name}}</option>
                            @endforeach
                        </select>
                    </div>

    
                        <button class="mt-3 btn btn-primary font-weight-bold" type="submit">Create</button>
                </form>
                </div>
            </div>
        </div>
    </div>

    
@endsection