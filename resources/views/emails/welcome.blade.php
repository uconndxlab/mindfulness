@extends('emails.layouts.base')

@section('title', 'Welcome')

@section('content')
    <h1 style="color: #1a492d; margin-bottom: 20px;">Welcome to {{ config('app.name') }}!</h1>

    <p style="margin-bottom: 15px;">
        We're glad you're here. This email includes a list of brief resources to help you get started and attachments if you want to download them.
    </p>

    <p style="margin-bottom: 15px;">
        You can revisit them anytime on the app's <a href="{{ route('help') }}" style="color: #48745D; font-weight: bold; text-decoration: underline;">About page</a>. If you have already reviewed them, you're all set to begin.
    </p>

    <p style="margin-bottom: 12px; font-weight: bold; color: #1a492d;">
        Before you start, please review these resources:
    </p>

    <ol style="margin: 0 0 8px; padding-left: 22px;">
        <li style="margin-bottom: 16px;">
            <strong style="color: #333;">App Tutorial (8.5 minutes)</strong>
            <div style="font-size: 14px; color: #666; margin: 2px 0 6px; line-height: 1.35;">A quick tour of the app and its main features.</div>
            <a href="{{ route('help') }}#tutorial" style="color: #48745D; font-weight: bold; text-decoration: underline;">Watch the tutorial</a>
        </li>
        <li style="margin-bottom: 16px;">
            <strong style="color: #333;">Welcome Letter (7 minutes)</strong>
            <div style="font-size: 14px; color: #666; margin: 2px 0 6px; line-height: 1.35;">A note from the team to help you begin. Also attached to this email.</div>
            <a href="{{ route('help') }}#welcome-letter" style="color: #48745D; font-weight: bold; text-decoration: underline;">Read the letter</a>
        </li>
        <li style="margin-bottom: 8px;">
            <strong style="color: #333;">A Brief Welcome from the Researcher (1 minute)</strong>
            <div style="font-size: 14px; color: #666; margin: 2px 0 6px; line-height: 1.35;">Meet the researcher in a video.</div>
            <a href="{{ route('help') }}#researcher" style="color: #48745D; font-weight: bold; text-decoration: underline;">Watch the video</a>
        </li>
    </ol>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('app.start') }}" style="display: inline-block; background-color: #48745D; color: #ffffff; padding: 10px 28px; text-decoration: none; border-radius: 100px; font-weight: bold; width: 100%; max-width: 360px; margin: 10px 0; box-sizing: border-box;">
            Ready to Start? Open the App Now
        </a>
    </div>

    <h2 style="color: #1a492d; font-size: 18px; margin: 28px 0 12px;">Don't forget your workbook!</h2>
    <p style="margin-bottom: 15px;">
        Alongside the app, this workbook offers another way to practice and strengthen your skills. Use it in whatever way feels helpful throughout your journey. A digital copy is attached, and we will mail you a printed copy soon.
    </p>

    <p style="margin: 28px 0 6px;">
        Warmly,
    </p>
    <p style="margin: 0 0 15px; font-weight: bold; color: #1a492d;">
        The {{ config('app.name') }} Team
    </p>
@endsection
