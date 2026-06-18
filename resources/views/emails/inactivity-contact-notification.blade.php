@extends('emails.layouts.admin')

@section('title', 'User Inactivity Alert')

@section('content')
    <h1 style="color: #1a492d; margin-bottom: 20px;">User Inactivity Alert</h1>

    <p style="margin-bottom: 15px;">
        The following user has been inactive for <strong>{{ $inactiveDays }} days</strong> and has reached the final inactivity milestone.
    </p>

    <ul style="margin-bottom: 20px; padding-left: 20px;">
        <li><strong>Name:</strong> {{ $user->name }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>HH ID:</strong> {{ $user->hh_id }}</li>
        <li><strong>Last active:</strong> {{ $user->last_active_at?->format('M j, Y g:i A') ?? 'Unknown' }}</li>
    </ul>

    <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
        No further automated inactivity emails will be sent for this user unless they log in again.
    </p>
@endsection
