@extends('emails.layouts.base')

@section('title', 'We Miss You!')

@section('content')
    <h1 style="color: #1a492d; margin-bottom: 20px;">Hi {{ $user->name }}!</h1>

    <p style="margin-bottom: 15px;">
        {{ $body }}
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/login') }}" style="display: inline-block; background-color: #48745D; color: #ffffff; padding: 10px 50px; text-decoration: none; border-radius: 100px; font-weight: bold;width: 100%;max-width: 300px;margin: 10px 0;box-sizing: border-box;">
            Login Now
        </a>
    </div>

    <p style="margin-bottom: 15px;">
        If you're having any issues with your account or have questions, we're here to help!
    </p>
@endsection