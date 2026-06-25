@extends('emails.layouts.admin')

@section('title', 'Participant Drop Out Risk Alert')

@section('recipient_emails')
    {{ implode(', ', config('mail.inactivity_alert_emails') ?: array_filter([config('mail.contact_email')])) }}
@endsection

@section('content')
    <h1 style="color: #1a492d; margin-bottom: 20px;">IMPORTANT - Participant drop out risk alert</h1>

    <p style="margin-bottom: 15px;">
        A participant has been inactive for <strong>{{ $inactiveDays }} days</strong> and is at risk of dropping out of the study.
    </p>
    
    <ul style="margin-bottom: 20px; padding-left: 20px;">
        <li><strong>Participant ID:</strong> {{ $user->hh_id ?? $user->id }}</li>
        <li><strong>Last Active:</strong> {{ $user->last_active_at?->format('Y-m-d H:i:s') }}</li>
    </ul>

    <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
        No further automated inactivity emails will be sent for this participant unless they log in again.
    </p>
@endsection
