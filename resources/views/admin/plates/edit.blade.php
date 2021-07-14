@extends('layouts.app')
@section('content')

    <div class="container">
        
        <div class="row">
            <div class="col-md-8 offset-md-2">
                
                <h1 class="my-4">CREATE NEW PLATE</h1>

                <form action="{{ route('admin.plates.update', $plate->id)}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')


                    {{-- Name --}}
                    <div class="my-4">
                        <label class="form-label" for="name">Name*</label>
                        <input class="form-control @error('name')
                            is-invalid
                        @enderror" type="text" id="name" name="name" value="{{old('name', $plate->name)}}">
                    </div>

                    {{-- description --}}
                    <div class="my-4">
                        <label class="form-label" for="description">description*</label>
                        <textarea class="form-control" @error('description') is-invalid @enderror name="description"
                         id="description" cols="30" rows="4">{{old('description', $plate->description)}}
                        </textarea>
                    </div>

                    {{-- ingredients --}}
                    <div class="my-4">
                        <label class="form-label" for="ingredients">ingredients*</label>
                        <input class="form-control @error('ingredients')
                            is-invalid
                        @enderror" type="text" id="ingredients" name="ingredients" value="{{old('ingredients', $plate->ingredients)}}">
                    </div>

                {{-- price --}}
                    <div class="my-4">
                        <label class="form-label" for="price">price*</label>
                        <input class="form-control @error('price')
                            is-invalid
                        @enderror" type="text" id="price" name="price" value="{{old('price', $plate->price)}}">
                    </div>


                    @error('types')
                    <div>{{ $message }}</div>
                        
                    @enderror
                    {{-- Add plate Image --}}
                    <div class="my-4">
                        <div>
                            <label for="img" class="form-label">plate Image</label>
                        </div>
    
                     @if ($plate->img)
                        <div class="mb-3">
                            <img width="200" src="{{ asset('storage/' . $plate->img) }}" alt="{{ $plate->name }}">
                        </div>
                    @endif
    
                        <input type="file" name="img" id="img">
                        @error('img')
                            <div>{{ $message }}</div>
                        @enderror
                    </div>
    
                        <button class="mt-3 btn btn-primary" type="submit">Update</button>
                </form>
                </div>
            </div>
        </div>
    </div>

    
@endsection