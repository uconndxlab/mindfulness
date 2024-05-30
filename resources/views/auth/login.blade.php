@extends('layouts.auth')

@section('title', 'Log In')

@section('content')
<div class="col-md-4">
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="text-left fs-5 fw-bold mb-3">
            Create an Account
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold" for="email">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror @error('password') is-invalid @enderror" name="email" value="{{ old('email') }}">
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold" for="password">Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>


        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">LOG IN</button>
        </div>
    </form>
    <div class="text-center mt-3">
        <hr class="my-4">
        <span class="text-muted">OR</span>
    </div>
    <div class="text-center mt-3">
        <a class="btn btn-info text-center" href="{{ route('register') }}">SIGN UP</a>
    </div>
</div>
@endsection
