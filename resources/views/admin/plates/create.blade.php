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
                        
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>    
                        @enderror
                    </div>

                    {{-- description --}}
                    <div class="my-2">
                        <label class="form-label" for="description">description*</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description" cols="30" rows="4">{{old('description')}}</textarea>
                    
                        @error('description')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>    
                        @enderror
                    </div>

                    {{-- ingredients --}}
                    <div class="my-2">
                        <label class="form-label" for="ingredients">ingredients*</label>
                        <input class="form-control @error('ingredients')
                            is-invalid
                        @enderror" type="text" id="ingredients" name="ingredients" value="{{old('ingredients')}}">

                        @error('ingredients')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>    
                        @enderror
                    </div>

                     {{-- price --}}
                    <div class="my-2">
                        <label class="form-label" for="price">price*</label>
                        <input class="form-control @error('price')
                            is-invalid
                        @enderror" type="text" id="price" name="price" value="{{old('price')}}">

                        @error('price')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>    
                        @enderror
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
                    {{-- default same of previus view --}}
                        <select id="select" name="restaurant_id">  
                            <option value="{{$prev_view_res_id}}" selected></option> 
                        </select>

    
                    <input class="mt-3 btn btn-success font-weight-bold" type="submit" value="create" />
                </form>
                </div>
            </div>
        </div>
    </div>


    
    <style>
        select{
            display: none
        } 
    </style>
@endsection