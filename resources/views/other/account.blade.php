@extends('layouts.app')

@section('title', 'Account')

@section('content')
<div class="col-md-8">
    <h4 class="text-center fw-bold mb-3">
        Progress
    </h4>
    @foreach ($modules as $module)
        <div class="prior-note">
            <div class="grey-note">
                <h5 class="fw-bold">
                    <span>Part {{ $module->order }} - {{ $module->name }}</span>
                </h5>
                    <ul class="text-muted ps-2 mb-0">
                        @if($module->totalDays > 0)
                            <li class="list-check{{ $module->daysCompleted == $module->totalDays ? '-filled fw-bold' : '' }}">{{ $module->daysCompleted }}/{{ $module->totalDays }} Days</li>
                        @endif
                        @if ($module->totalCheckInActivities > 0)
                            <li class="list-check{{ $module->completedCheckInActivities == $module->totalCheckInActivities ? '-filled fw-bold' : '' }}">{{ $module->completedCheckInActivities }}/{{ $module->totalCheckInActivities }} Quick Check-Ins</li>
                        @endif
                        @if ($module->totalCheckInDays > 0)
                            <li class="list-check{{ $module->completedCheckInDays == $module->totalCheckInDays ? '-filled fw-bold' : '' }}">{{ $module->completedCheckInDays }}/{{ $module->totalCheckInDays }} Rate My Awareness</li>
                        @endif
                    </ul>
            </div>
        </div>
    @endforeach
    <div class="priority-note">
        <div class="grey-note">
            <h5 class="fw-bold">
                <span>Stats</span>
            </h5>
            <div class="stats-container col-xl-6 col-lg-6 col-md-8">
                <div class="stat-item">
                    <span class="stat-label">Average Quick Check-In Score</span>
                    <span class="stat-value">{{ $stats['pq_check_ins'] ? number_format($stats['pq_check_ins'], 1).'%' : 'N/A' }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Total Quick Check-Ins</span>
                    <span class="stat-value">{{ $stats['count_check_ins'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Average Rate My Awareness Score</span>
                    <span class="stat-value">{{ $stats['pq_rmas'] ? number_format($stats['pq_rmas'], 1).'%' : 'N/A' }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Total Rate My Awareness</span>
                    <span class="stat-value">{{ $stats['count_rmas'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Overall Average Score</span>
                    <span class="stat-value">{{ $stats['pq_avg'] ? number_format($stats['pq_avg'], 1).'%' : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <h4 class="text-center fw-bold mt-5 mb-3">My Account</h4>
    @livewire('account-update-form')
    
    @if (Auth::user()->isAdmin())
        <div class="text-center mt-3">
            <div class="form-group">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">ADMIN DASHBOARD</a>
            </div>
        </div>
    @endif
</div>
@endsection
