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
                    <span>{{ $module->partName() }}</span>
                </h5>
                <ul class="text-muted ps-2 mb-0">
                    @if($module->totalDays > 0)
                        <li class="list-check{{ $module->daysCompleted == $module->totalDays ? '-filled fw-bold' : '' }}">{{ $module->daysCompleted }}/{{ $module->totalDays }} Days</li>
                    @endif
                    @if ($module->totalCheckInActivities > 0)
                        <li class="list-check{{ $module->completedCheckInActivities == $module->totalCheckInActivities ? '-filled fw-bold' : '' }}">{{ $module->completedCheckInActivities }}/{{ $module->totalCheckInActivities }} Quick Check-Ins</li>
                    @endif
                    @if ($module->totalSelfRatings > 0)
                        <li class="list-check{{ $module->completedSelfRatings == $module->totalSelfRatings ? '-filled fw-bold' : '' }}">{{ $module->completedSelfRatings }}/{{ $module->totalSelfRatings }} Self-Rating</li>
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
                    <div class="text-end">
                        <div class="stat-value">{{ $stats['check_ins']['average'] ? number_format($stats['check_ins']['average'], 1) : '--' }}%</div>
                        <div class="text-muted small">Over <span class="fw-bold">{{ $stats['check_ins']['count'] }}</span> check-in{{ $stats['check_ins']['count'] != 1 ? 's' : '' }}</div>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Average Self-Rating Score</span>
                    <div class="text-end">
                        <div class="stat-value">{{ $stats['self_ratings']['average'] ? number_format($stats['self_ratings']['average'], 1) : '--' }}%</div>
                        <div class="text-muted small">Over <span class="fw-bold">{{ $stats['self_ratings']['count'] }}</span> self-rating{{ $stats['self_ratings']['count'] != 1 ? 's' : '' }}</div>
                    </div>
                </div>
                <div class="stats-subset">
                    <div class="stat-item stat-item-child">
                        <span class="stat-label">Rate My Emotions</span>
                        <div class="text-end">
                            <div class="stat-value">{{ $stats['self_ratings']['emotions']['average'] ? number_format($stats['self_ratings']['emotions']['average'], 1) : '--' }}%</div>
                            <div class="text-muted small">Over <span class="fw-bold">{{ $stats['self_ratings']['emotions']['count'] }}</span> self-rating{{ $stats['self_ratings']['emotions']['count'] != 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                    <div class="stat-item stat-item-child">
                        <span class="stat-label">Rate My Presence in Parenting</span>
                        <div class="text-end">
                            <div class="stat-value">{{ $stats['self_ratings']['parenting']['average'] ? number_format($stats['self_ratings']['parenting']['average'], 1) : '--' }}%</div>
                            <div class="text-muted small">Over <span class="fw-bold">{{ $stats['self_ratings']['parenting']['count'] }}</span> self-rating{{ $stats['self_ratings']['parenting']['count'] != 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                    <div class="stat-item stat-item-child">
                        <span class="stat-label">Rate My Awareness</span>
                        <div class="text-end">
                            <div class="stat-value">{{ $stats['self_ratings']['awareness']['average'] ? number_format($stats['self_ratings']['awareness']['average'], 1) : '--' }}%</div>
                            <div class="text-muted small">Over <span class="fw-bold">{{ $stats['self_ratings']['awareness']['count'] }}</span> self-rating{{ $stats['self_ratings']['awareness']['count'] != 1 ? 's' : '' }}</div>
                        </div>
                    </div>
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
