@extends('emails.layouts.base')

@section('title', 'You\'re Invited!')

@section('content')
    <h1 style="color: #1a492d; margin-bottom: 20px;">Welcome to {{ config('app.name') }}!</h1>

    <p style="margin-bottom: 15px;">
        You've been invited to join <strong>{{ config('app.name') }}</strong>, a mindfulness and wellness platform designed to support your journey toward healing and personal growth.
    </p>

    <p style="margin-bottom: 15px;">
        Click the button below to create your account and get started:
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $registrationUrl }}" style="display: inline-block; background-color: #48745D; color: #ffffff; padding: 10px 50px; text-decoration: none; border-radius: 100px; font-weight: bold;width: 100%;max-width: 300px;margin: 10px 0;box-sizing: border-box;">
            Create Your Account
        </a>
    </div>

    <p style="margin-bottom: 15px; font-size: 14px; color: #666;">
        <strong>Important:</strong> This invitation link will expire in {{ config('invitations.expiration_days') }} days.
    </p>

    <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
        If you have any questions or need assistance, please don't hesitate to reach out to us.
    </p>

    <p style="margin-bottom: 15px; color: #999; font-size: 12px;">
        If the button doesn't work, you can copy and paste this link into your browser:<br>
        <a href="{{ $registrationUrl }}" style="color: #48745D; word-break: break-all;">{{ $registrationUrl }}</a>
    </p>
@endsection
