@extends('layouts.app')

@section('title', 'Account')

@section('content')
@php
    $formatAverage = fn (?float $average) => $average ? number_format($average, 1) . '%' : '--%';
    $isComplete = fn (int $completed, int $total) => $total > 0 && $completed === $total;
    $totals = $progress['totals'];
@endphp
<div class="col-md-8">
    <h4 class="text-center fw-bold mb-3">
        Progress
    </h4>

    <div class="prior-note mb-4">
        <div class="grey-note progress-dashboard">
            <div class="progress-parts-grid">
                @foreach ($progress['parts'] as $part)
                    <div class="progress-part">
                        <h6 class="progress-part-title fw-bold mb-2">{{ $part['short_name'] }}</h6>
                        <ul class="text-muted ps-2 mb-0">
                            @if ($part['days']['total'] > 0)
                                <li class="list-check{{ $isComplete($part['days']['completed'], $part['days']['total']) ? '-filled fw-bold' : '' }}">
                                    {{ $part['days']['completed'] }}/{{ $part['days']['total'] }} Days
                                </li>
                            @endif
                            @if ($part['check_ins']['total'] > 0)
                                <li class="list-check{{ $isComplete($part['check_ins']['completed'], $part['check_ins']['total']) ? '-filled fw-bold' : '' }}">
                                    {{ $part['check_ins']['completed'] }}/{{ $part['check_ins']['total'] }} Quick Check-Ins
                                </li>
                            @endif
                            @if ($part['self_ratings']['total'] > 0)
                                <li class="list-check{{ $isComplete($part['self_ratings']['completed'], $part['self_ratings']['total']) ? '-filled fw-bold' : '' }}">
                                    {{ $part['self_ratings']['completed'] }}/{{ $part['self_ratings']['total'] }} Self-Rating{{ $part['self_ratings']['total'] != 1 ? 's' : '' }}
                                </li>
                            @endif
                        </ul>
                        @if ($part['check_ins']['total'] > 0)
                            <div class="progress-part-metric">
                                <span class="progress-part-metric-label">Avg. check-in</span>
                                <span class="progress-part-metric-value">{{ $formatAverage($part['check_ins']['average']) }}</span>
                            </div>
                        @endif
                        @if ($part['self_ratings']['total'] > 0)
                            <div class="progress-part-metric">
                                <span class="progress-part-metric-label">Avg. self-rating</span>
                                <span class="progress-part-metric-value">{{ $formatAverage($part['self_ratings']['average']) }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="progress-totals">
                <h6 class="progress-totals-title fw-bold mb-2">Totals</h6>
                @if ($totals['days']['total'] > 0)
                    <div class="stat-item">
                        <span class="stat-label">Days</span>
                        <div class="text-end">
                            <div class="stat-value">{{ $totals['days']['completed'] }}/{{ $totals['days']['total'] }}</div>
                        </div>
                    </div>
                @endif
                <div class="stats-container">
                    <div class="stat-item">
                        <span class="stat-label">Average Quick Check-In Score</span>
                        <div class="text-end">
                            <div class="stat-value">{{ $formatAverage($totals['check_ins']['average']) }}</div>
                            <div class="text-muted small">Over <span class="fw-bold">{{ $totals['check_ins']['count'] }}</span> check-in{{ $totals['check_ins']['count'] != 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Average Self-Rating Score</span>
                        <div class="text-end">
                            <div class="stat-value">{{ $formatAverage($totals['self_ratings']['average']) }}</div>
                            <div class="text-muted small">Over <span class="fw-bold">{{ $totals['self_ratings']['count'] }}</span> self-rating{{ $totals['self_ratings']['count'] != 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                    <div class="stats-subset">
                        <div class="stat-item stat-item-child">
                            <span class="stat-label">Rate My Emotions</span>
                            <div class="text-end">
                                <div class="stat-value">{{ $formatAverage($totals['self_ratings']['emotions']['average']) }}</div>
                                <div class="text-muted small">Over <span class="fw-bold">{{ $totals['self_ratings']['emotions']['count'] }}</span> self-rating{{ $totals['self_ratings']['emotions']['count'] != 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                        <div class="stat-item stat-item-child">
                            <span class="stat-label">Rate My Presence in Parenting</span>
                            <div class="text-end">
                                <div class="stat-value">{{ $formatAverage($totals['self_ratings']['parenting']['average']) }}</div>
                                <div class="text-muted small">Over <span class="fw-bold">{{ $totals['self_ratings']['parenting']['count'] }}</span> self-rating{{ $totals['self_ratings']['parenting']['count'] != 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                        <div class="stat-item stat-item-child">
                            <span class="stat-label">Rate My Awareness</span>
                            <div class="text-end">
                                <div class="stat-value">{{ $formatAverage($totals['self_ratings']['awareness']['average']) }}</div>
                                <div class="text-muted small">Over <span class="fw-bold">{{ $totals['self_ratings']['awareness']['count'] }}</span> self-rating{{ $totals['self_ratings']['awareness']['count'] != 1 ? 's' : '' }}</div>
                            </div>
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
