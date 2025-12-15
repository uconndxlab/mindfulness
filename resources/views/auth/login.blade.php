@extends('layouts.auth')

@section('title', 'Log In')
@section('page_id', 'auth-login-register')

@section('content')
<div class="col-md-6">
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        
        @if ($errors->has('credentials') || $errors->has('error'))
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="text-left fs-2 fw-bold mb-1">
            {{ config('app.name') }}
        </div>
        <div class="text-left fs-5 fw-bold mb-3">
            Log in to your Account
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold" for="email">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror @error('login') is-invalid @enderror" name="email" value="{{ old('email') }}">
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold" for="password">Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror @error('login') is-invalid @enderror" name="password">
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
            @error('login')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('password.request') }}" class="text-center text- mt-1 mb-2">Forgot Password?</a>

            <div class="form-check mt-1 mb-2">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>
        </div>

        <input type="hidden" name="timezone" id="timezone">

        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">LOG IN</button>
        </div>
    </form>
    @if (!getConfig('registration_locked', false))
        <div class="text-center mt-3">
            <hr class="my-4">
            <span class="text-muted">OR</span>
        </div>
        <div class="text-center mt-3">
            <a class="btn btn-info text-center" href="{{ route('register') }}">SIGN UP</a>
        </div>
    @endif
</div>
@endsection
