@extends('layouts.auth')

@section('title', 'New User')

@section('content')
<div class="col-md-6">
    <form method="POST" action="{{ route('register.submit') }}">
        @csrf

        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        <div class="text-left fs-5 fw-bold mb-3">
            Create an Account
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold mb-1" for="name">First Name</label>
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autocomplete="off">
            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold mb-1" for="email">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="off">
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold mb-1" for="password">Password</label><br>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="off">
            <small>(Must contain at least 8 characters, one uppercase letter, one lowercase letter, and one number)</small>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="d-flex justify-content-end">
            <div class="form-check mt-1 mb-2">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>
        </div>
            
        <div class="form-group text-center mb-3">
            <button type="submit" class="btn btn-primary">SIGN UP</button>
        </div>
    </form>

    <a href="{{ route('login') }}" class="text-center text- mt-3">Return to Login</a>
</div>
@endsection
