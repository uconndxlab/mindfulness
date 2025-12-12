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

    <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
        If you have any questions or need assistance, please don't hesitate to reach out to us.
    </p>

    <p style="margin-bottom: 15px; color: #999; font-size: 12px;">
        If the button doesn't work, you can copy and paste this link into your browser:<br>
        <a href="{{ url('/login') }}" style="color: #48745D; word-break: break-all;">{{ url('/login') }}</a>
    </p>
@endsection