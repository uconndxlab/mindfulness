@extends('layouts.app')

@section('title', 'Bonus Activities')
@section('page_id', 'bonus')

@section('content')
<div class="col-md-8">
    <div class="text-left mb-3">
        <h1 class="display fw-bold mb-1">Bonus Activities</h1>
        <p>This is a collection of bonus activities that you have unlocked on your journey. They are not required to completed, but offer additional insights and opportunities to learn and grow.</p>
    </div>

    <div class="mb-2">
        <h5>Progress:</h5>
        <ul>
            <li class="list-check{{ $stats['numberBonusUnlocked'] == $stats['totalBonus'] ? '-filled' : '' }}">{{ $stats['numberBonusUnlocked'] }}/{{ $stats['totalBonus'] }} Activities Unlocked</li>
            <li class="list-check{{ $stats['numberBonusCompleted'] == $stats['totalBonus'] ? '-filled' : '' }}">{{ $stats['numberBonusCompleted'] }}/{{ $stats['totalBonus'] }} Activities Completed</li>
        </ul>
    </div>
    <div class="accordion accordion-flush mb-3" id="accordionDays" data-accordion-activity="{{ $accordion_activity_id ?? '' }}">
        @foreach ($activitiesByDay as $groupKey => $group)
            @php
                $day = $group['day'];
                $disabled = $day->unlocked ? '' : 'disabled';
            @endphp

            <div class="accordion-item border mb-2" id="day_{{ $day->id }}">
                <h2 class="accordion-header" id="heading_{{ $groupKey }}">
                    <button class="accordion-button {{ $day->active ? '' : 'collapsed' }} {{ $disabled }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $groupKey }}" aria-expanded="{{ $day->active ? 'true' : 'false' }}" aria-controls="collapse_{{ $groupKey }}">
                        <div class="d-flex w-100">                                
                            @if ($day->completed)
                                <i class="bi bi-check-square-fill"></i>
                            @elseif($disabled)
                                <i class="bi bi-lock-fill"></i>
                            @else
                                <i class="bi bi-square-fill"></i>
                            @endif
                            <div class="flex-grow-1 pe-4">
                                <div class="text-dark fw-bold">{{ $day->name }}</div>
                                <div class="text-dark fw-normal">{{ $day->description }}</div>
                            </div>
                        </div>
                    </button>
                </h2>
                
                <div id="collapse_{{ $groupKey }}" class="accordion-collapse collapse {{ $day->active ? 'show' : '' }}" aria-labelledby="heading_{{ $groupKey }}" data-bs-parent="#accordionDays">
                    <div class="accordion-body">
                        @if (!$disabled)
                            @foreach ($group['activities'] as $activity)
                                @php
                                    $disabled = $activity->unlocked ? '' : 'disabled';
                                @endphp
                                <div class="card p-2 module mb-2">
                                    <div class="flex-grow-1">
                                        <a id="moduleLink_{{ $activity->id }}" class="stretched-link w-100 activity-link {{ $disabled }} pb-1" data-id="{{ $activity->id }}" href="#">
                                            <div class="d-flex">
                                                @if ($activity->completed)
                                                    <i class="bi bi-check-square-fill"></i>
                                                @else
                                                    <i class="bi bi-square-fill"></i>
                                                @endif
                                                <div class="flex-grow-1">
                                                    <p class="activity-font mb-1">{{ $activity->title }}</p>
                                                    <div>
                                                        @if ($activity->type)
                                                            <span class="sub-activity-font activity-tag-{{ $activity->type }}">{{ ucfirst($activity->type) }}</span>
                                                        @endif
                                                        @if (isset($activity->time))
                                                            @if ($activity->time >= 1)
                                                                <span class="sub-activity-font activity-tag-time">{{ $activity->time.' min' }}</span>
                                                            @else
                                                                <span class="sub-activity-font activity-tag-time">{{ '<1 min' }}</span>
                                                            @endif
                                                        @endif
                                                        @if ($activity->optional)
                                                            <span class="sub-activity-font activity-tag-optional">Bonus</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                        @if ($activity->unlocked && $activity->description)
                                            <div class="activity-description-accordion col-md-4">
                                                <div class="accordion-item border-0 activity-description-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button activity-description-button {{ $accordion_activity_id == $activity->id ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_description_{{ $activity->id }}" aria-expanded="false" aria-controls="collapse_description_{{ $activity->id }}">
                                                            <i class="bi bi-info-circle me-2"></i>
                                                            <small>Learn more</small>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse_description_{{ $activity->id }}" class="accordion-collapse collapse {{ $accordion_activity_id == $activity->id ? 'show' : '' }}">
                                                        <div class="accordion-body activity-description-body">
                                                            {{ $activity->description }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <i class="bi bi-arrow-right"></i>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
