@extends('layouts.main')

@section('title', 'Content Upload')

@section('content')
<div class="col-md-6">
    <div class="text-left">
        <h1 class="display font-weight-bold">Content Upload:</h1>
    </div>

    @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.upload') }}">
        @csrf
        
        <div class="form-group">
            <label for="title">Title</label>
            <input id="title" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}">
            @error('title')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
                </span>
                @enderror
        </div>
        
        <div class="text-center">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">SAVE</button>
            </div>
        </div>
        </form>
</div>
@endsection
