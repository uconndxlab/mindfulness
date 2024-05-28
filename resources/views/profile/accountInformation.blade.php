@extends('layouts.main')

@section('title', 'Profile')

@section('content')
<div class="col-md-4">
    @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('user.update.namePass') }}">
        @csrf
        @Method('put')
        
        <div class="form-group">
            <label for="name">Name</label>
            <input id="name" type="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', Auth::user()->name) }}">
            @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
                </span>
                @enderror
        </div>
        
        <div class="form-group">
            <label for="password">New Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
            @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="oldPass">Enter old password to confirm changes:</label>
            <input id="oldPass" type="password" class="form-control @error('oldPass') is-invalid @enderror" name="oldPass">
            @error('oldPass')
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

    @if (auth::user()->isAdmin())
        <div class="text-center">
            <div class="form-group">
                <a href="{{ route('admin.browse') }}" class="btn btn-primary disabled">ADMIN: Content Upload</a>
            </div>
        </div>
    @endif
</div>
@endsection
