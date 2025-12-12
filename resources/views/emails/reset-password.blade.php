@extends('emails.layouts.base')

@section('title', 'Reset Your Password')

@section('content')
    <h1 style="color: #1a492d; margin-bottom: 20px;">Hi {{ $user->name }}!</h1>

    <p style="margin-bottom: 15px;">
        You are receiving this email because we received a password reset request for your account.
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $resetUrl }}" style="display: inline-block; background-color: #48745D; color: #ffffff; padding: 10px 50px; text-decoration: none; border-radius: 100px; font-weight: bold;width: 100%;max-width: 300px;margin: 10px 0;box-sizing: border-box;">
            Reset Password
        </a>
    </div>

    <p style="margin-bottom: 15px;">
        If you did not request a password reset, no further action is required.
    </p>

    <p style="margin-bottom: 15px; font-size: 14px; color: #666;">
        <strong>Important:</strong> This password reset link will expire in 60 minutes.
    </p>

    <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
        If you have any questions or need assistance, please don't hesitate to reach out to us.
    </p>

    <p style="margin-bottom: 15px; color: #999; font-size: 12px;">
        If the button doesn't work, you can copy and paste this link into your browser:<br>
        <a href="{{ $resetUrl }}" style="color: #48745D; word-break: break-all;">{{ $resetUrl }}</a>
    </p>
@endsection 