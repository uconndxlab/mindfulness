@extends('layouts.auth')

@section('title', 'Email Verification')
@section('page_id', 'auth-verify')

@section('content')
    <div class="col-md-6">
        @if (session('message'))
            <div class="alert alert-success" role="alert">
                {{ session('message') }}
            </div>
        @endif

        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        <h1>Email Verification</h1>

        <p>Please check your email for a verification link.</p>
        <p>If you did not receive the email, click the button below to request another.</p>

        <!-- verification.resend -->
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">Resend Verification Email</button>
        </form>
    </div>
@endsection
