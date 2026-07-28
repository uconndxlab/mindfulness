@extends('layouts.app')

@section('title', 'Home')
@section('page_id', 'home')

@section('content')
@php
    $currentModule = $modules->first(fn ($m) => $m->unlocked && ! $m->completed)
        ?? $modules->last(fn ($m) => $m->unlocked)
        ?? $modules->first();
    $currentColorSlug = $currentModule->flowerColorSlug();

    $placeholderDates = [
        1 => ['completed' => 'Completed: July 15, 2026'],
        2 => ['active' => 'To Complete On: July 22, 2026'],
        3 => ['locked' => 'Start: July 22, 2026'],
        4 => ['locked' => 'Start: July 29, 2026'],
    ];
@endphp

<div class="col-md-8 home-page home-theme--{{ $currentColorSlug }}">
    <div class="home-flowers">
        @foreach ($modules as $module)
            <img src="{{ $module->flowerFrameUrl($module->daysCompleted) }}"
                alt="{{ $module->flowerColorName() }} Flower"
                class="home-flower-icon">
        @endforeach
    </div>

    <h1 class="home-title">{{ config('app.name') }}</h1>

    <p class="home-intro">
        You started the {{ config('app.name') }} journey on July 8, 2026. Your goal is to finish the journey on August 5, 2026.
    </p>

    <div class="home-goal">
        <p class="home-goal-label">Today's Goal</p>
        <p class="home-goal-heading">Make progress in Part {{ $currentModule->order }}: {{ $currentModule->flowerColorName() }} Flower.</p>
        <p class="home-goal-note">Set intention to come back to the app everyday.</p>
    </div>

    <div class="home-progress">
        <div class="home-progress-labels">
            <span>To Next Flower</span>
            <span>10% left</span>
        </div>
        <div class="home-progress-bar">
            <div class="home-progress-fill"></div>
        </div>
    </div>

    <div class="home-modules">
        @foreach ($modules as $module)
            @php
                $colorSlug = $module->flowerColorSlug();
                $moduleTitle = 'Part '.$module->order.': '.$module->flowerColorName().' Flower';

                if ($module->completed) {
                    $statusText = $placeholderDates[$module->order]['completed'] ?? 'Completed: July 15, 2026';
                } elseif ($module->unlocked) {
                    $statusText = $placeholderDates[$module->order]['active'] ?? 'To Complete On: July 22, 2026';
                } else {
                    $statusText = $placeholderDates[$module->order]['locked'] ?? 'Start: July 22, 2026';
                }
            @endphp

            <div class="home-module-card{{ $module->unlocked ? '' : ' home-module-card--locked' }}">
                @if ($module->unlocked)
                    <a href="{{ route('explore.module', ['module_id' => $module->id]) }}"
                        class="home-module-link stretched-link">
                        <div class="home-module-content">
                            <h6 class="home-module-title">{{ $moduleTitle }}</h6>
                            <p class="home-module-status">{{ $statusText }}</p>
                            <ul class="home-module-stats">
                                <li class="list-check{{ $module->daysCompleted == $module->totalDays ? '-filled list-check-filled--'.$colorSlug : '' }}">{{ $module->daysCompleted }}/{{ $module->totalDays }} Days</li>
                                @if ($module->totalCheckInActivities > 0)
                                    <li class="list-check{{ $module->completedCheckInActivities == $module->totalCheckInActivities ? '-filled list-check-filled--'.$colorSlug : '' }}">{{ $module->completedCheckInActivities }}/{{ $module->totalCheckInActivities }} Quick Check-Ins</li>
                                @endif
                                @if ($module->totalSelfRatings > 0)
                                    <li class="list-check{{ $module->completedSelfRatings == $module->totalSelfRatings ? '-filled list-check-filled--'.$colorSlug : '' }}">{{ $module->completedSelfRatings }}/{{ $module->totalSelfRatings }} Self-Rating</li>
                                @endif
                            </ul>
                        </div>
                    </a>
                @else
                    <a href="#"
                        class="home-module-link stretched-link locked-module-link"
                        data-module-name="{{ $moduleTitle }}">
                        <div class="home-module-content">
                            <h6 class="home-module-title">{{ $moduleTitle }}</h6>
                            <p class="home-module-status">{{ $statusText }}</p>
                            <ul class="home-module-stats">
                                <li class="list-check">{{ $module->daysCompleted }}/{{ $module->totalDays }} Days</li>
                                @if ($module->totalCheckInActivities > 0)
                                    <li class="list-check">{{ $module->completedCheckInActivities }}/{{ $module->totalCheckInActivities }} Quick Check-Ins</li>
                                @endif
                                @if ($module->totalSelfRatings > 0)
                                    <li class="list-check">{{ $module->completedSelfRatings }}/{{ $module->totalSelfRatings }} Self-Rating</li>
                                @endif
                            </ul>
                        </div>
                    </a>
                @endif
                <i class="bi bi-chevron-right home-module-chevron"></i>
            </div>
        @endforeach

        @if ($bonusInfo['numberBonusUnlocked'] > 0)
            <hr>
            <div class="home-module-card">
                <a id="bonusLink" href="{{ route('explore.bonus') }}" class="home-module-link stretched-link">
                    <div class="home-module-content">
                        <h6 class="home-module-title">Bonus Activities</h6>
                        <ul class="home-module-stats">
                            <li class="list-check{{ $bonusInfo['numberBonusUnlocked'] == $bonusInfo['totalBonus'] ? '-filled list-check-filled--default' : '' }}">{{ $bonusInfo['numberBonusUnlocked'] }}/{{ $bonusInfo['totalBonus'] }} Activities Unlocked</li>
                            <li class="list-check{{ $bonusInfo['numberBonusCompleted'] == $bonusInfo['totalBonus'] ? '-filled list-check-filled--default' : '' }}">{{ $bonusInfo['numberBonusCompleted'] }}/{{ $bonusInfo['totalBonus'] }} Activities Completed</li>
                        </ul>
                    </div>
                </a>
                <i class="bi bi-chevron-right home-module-chevron"></i>
            </div>
        @endif
    </div>

    <div class="home-encouragement">
        <p class="home-encouragement-stats">
            You've returned to {{ config('app.name') }} for <span class="home-encouragement-highlight">XX</span> days and completed <span class="home-encouragement-highlight">XX</span> activities!
        </p>
        <p class="home-encouragement-thanks">Thank you for your commitment <i class="bi bi-heart-fill home-encouragement-heart"></i></p>
    </div>
</div>
@endsection
