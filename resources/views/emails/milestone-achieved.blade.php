@extends('emails.layouts.admin')

@section('title', 'Milestone Achieved')

@section('content')
    <h1 style="color: #1a492d; margin-bottom: 20px;">User Milestone Achieved</h1>

    <p style="margin-bottom: 15px;">
        A user has reached a new milestone in <strong>{{ config('app.name') }}</strong>.
    </p>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0 0 10px 0;"><strong>User:</strong> {{ $user->email }}</p>
        <p style="margin: 0;"><strong>Milestone:</strong> {{ $milestoneLabel }}</p>
    </div>

    <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
        This is an automated notification for admin tracking purposes.
    </p>
@endsection
