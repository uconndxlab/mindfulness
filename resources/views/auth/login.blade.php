@extends('layouts.auth')

@section('title', 'Log In')

@section('content')
<div class="col-md-4">
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror @error('password') is-invalid @enderror" name="email" value="{{ old('email') }}">
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>


        <div class="form-group">
            <button type="submit" class="btn btn-primary">LOG IN</button>
        </div>
    </form>
    <div class="text-center mt-3">
        <hr class="my-4">
        <span class="text-muted">OR</span>
    </div>
    <a class="btn btn-primary" href="{{ route('register') }}">SIGN UP</a>
</div>
@endsection
