@extends('layouts.auth')

@section('title', 'New User')
@section('page_id', 'auth-login-register')

@section('content')
<div class="col-md-6">
    <form method="POST" action="{{ route('register.submit') }}">
        @csrf

        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        @if(session('invitation_token') && isset($invitation))
            @if($invitation->expires_at->diffInHours() < 24)
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-clock"></i> Your invitation expires {{ $invitation->expires_at->diffForHumans() }}.
                </div>
            @endif
        @endif

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
            @if(session('invitation_email'))
                <input id="email" type="email"
                    class="form-control" 
                    value="{{ session('invitation_email') }}" 
                    readonly disabled>
                <input type="hidden" name="email" value="{{ session('invitation_email') }}">
                <small class="text-muted">Email is pre-filled from your invitation.</small>
            @else
                <input id="email" type="email"
                    class="form-control @error('email') is-invalid @enderror" 
                    name="email" 
                    value="{{ old('email') }}" 
                    autocomplete="off">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            @endif
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

        <div class="form-group mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="terms_accepted" name="terms_accepted" value="1">
                <label class="form-check-label" for="terms_accepted">
                    I have read and agree to the <a href="{{ Storage::url(config('terms.file_path')) }}" target="_blank" rel="noopener noreferrer">Terms of Use</a>
                </label>
            </div>
            @error('terms_accepted')
                <span class="invalid-feedback d-block" role="alert">
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
        
        <input type="hidden" name="timezone" id="timezone">
        @if(session('invitation_token'))
            <input type="hidden" name="invitation_token" value="{{ session('invitation_token') }}">
        @endif

        <div class="form-group text-center mb-3">
            <button type="submit" class="btn btn-primary">SIGN UP</button>
        </div>
    </form>

    <a href="{{ route('login') }}" class="text-center text- mt-3">Return to Login</a>
</div>
@endsection
