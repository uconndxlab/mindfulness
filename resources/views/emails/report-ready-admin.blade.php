@extends('emails.layouts.admin')

@section('title', 'Completion Report Ready')

@section('content')
    <h1 style="color: #1a492d; margin-bottom: 20px;">Completion Report Ready</h1>

    <p style="margin-bottom: 15px;">
        A participant has completed <strong>{{ config('app.name') }}</strong>. Their final journey report is ready for viewing in the admin dashboard.
    </p>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0 0 10px 0;"><strong>User:</strong> {{ $user->hh_id }}</p>
        @if ($completedAt)
            <p style="margin: 0;"><strong>Completed:</strong> {{ $completedAt->timezone(config('app.timezone'))->format('M d, Y g:i A T') }}</p>
        @endif
    </div>

    <p style="margin: 24px 0;">
        <a href="{{ $reportsUrl }}" style="display: inline-block; background-color: #48745D; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            View Completion Report
        </a>
    </p>

    <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
        This is an automated notification for admin tracking purposes.
    </p>
@endsection
