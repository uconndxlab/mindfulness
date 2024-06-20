@extends('layouts.auth')

@section('title', 'Email Verification')

@section('content')
<div class="col-md-6">
    @if (session('message'))
        <div class="alert alert-success" role="alert">
            {{ session('message') }}
        </div>
    @endif

    <h1>Email Verification</h1>

    <p>Please check your email for a verification link.</p>
    <p>If you did not receive the email, click the button below to request another.</p>

    <!-- verification.resend -->
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Resend Verification Email</button>
    </form>
    <p>For now, click to verify email ^</p>
</div>
@endsection
