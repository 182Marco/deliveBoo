@extends('layouts.app')
@section('content')

    <div class="container">
        
        <div class="row">
            <div class="col-md-8 offset-md-2">
                
                <h2 class="my-3 font-weight-bold">EDIT THE PLATE</h2>

                <a class="btn btn-primary font-weight-bold" href="{{ route('admin.plates.index', $plate->restaurant_id) }}">Plates List</a>

                <form class="delete-post-form font-weight-bold d-inline-block ml-3" action="{{ route('admin.plates.destroy', $plate->id )}}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input class="btn btn-danger btn-sm" type="submit" value="DELETE">
                </form>

                <form action="{{ route('admin.plates.update', $plate->id)}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')


                    {{-- Name --}}
                    <div class="my-2">
                        <label class="form-label" for="name">Name*</label>
                        <input class="form-control @error('name')
                            is-invalid
                        @enderror" type="text" id="name" name="name" value="{{old('name', $plate->name)}}">

                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>    
                        @enderror
                    </div>

                    {{-- description --}}
                    <div class="my-2">
                        <label class="form-label" for="description">description*</label>
                        <textarea class="form-control" @error('description') is-invalid @enderror name="description"
                         id="description" cols="30" rows="4">{{old('description', $plate->description)}}
                        </textarea>

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
                        @enderror" type="text" id="ingredients" name="ingredients" value="{{old('ingredients', $plate->ingredients)}}">
                    
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
                        @enderror" type="text" id="price" name="price" value="{{old('price', $plate->price)}}">

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
                        <input type="radio" name="visible" id="no_visible" value="0" @if(!old('visible')) checked @endif>
                    </div>

                    {{-- Add plate Image --}}
                    <div class="my-2">
                            <label for="img" class="form-label font-weight-bold">plate Image: </label> 
                     @if ($plate->img)
                        <div class="mb-3">
                            <img width="200" src="{{ asset('storage/' . $plate->img) }}" alt="{{ $plate->name }}">
                        </div>
                    @endif
                        <input class="d-inline-block ml-2" type="file" name="img" id="img">
                        @error('img')
                            <div>{{ $message }}</div>
                        @enderror
                    </div>
    
                        <button class="mt-3 btn btn-primary font-weight-bold" type="submit">Update</button>
                </form>
                </div>
            </div>
        </div>
    </div>

    
@endsection