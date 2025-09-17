@extends('layouts.app')

@section('title', $module->name)
@section('page_id', 'module')

@section('content')
<div class="col-md-8">
    <div class="text-left mb-3">
        <h1 class="display fw-bold mb-1">Part {{ $module->order }} - {{ $module->name }}</h1>
        <p>{{ $module->description }}</p>
        @if ($module->workbook_path)
            <x-pdf-viewer fpath="{{ Storage::url($module->workbook_path) }}" wbName="{{ $module->name }}" />
        @endif
    </div>

    <div class="mb-2">
        <h5>Progress:</h5>
        <ul>
            <li class="list-check{{ $module->daysCompleted == $module->totalDays ? '-filled' : '' }}">{{ $module->daysCompleted }}/{{ $module->totalDays }} Days</li>
            @if ($module->totalCheckInActivities > 0)
                <li class="list-check{{ $module->completedCheckInActivities == $module->totalCheckInActivities ? '-filled' : '' }}">{{ $module->completedCheckInActivities }}/{{ $module->totalCheckInActivities }} Quick Check-Ins</li>
            @endif
            @if ($module->totalCheckInDays > 0)
                <li class="list-check{{ $module->completedCheckInDays == $module->totalCheckInDays ? '-filled' : '' }}">{{ $module->completedCheckInDays }}/{{ $module->totalCheckInDays }} Rate My Awareness</li>
            @endif
        </ul>
    </div>
    <div class="accordion accordion-flush mb-3" id="accordionDays" data-accordion-activity="{{ $accordion_activity_id ?? '' }}">
        @foreach ($module->days as $index => $day)
            @php
                $disabled = $day->unlocked ? '' : 'disabled';
            @endphp

            <div class="accordion-item border mb-2" id="day_{{ $day->id }}">
                <h2 class="accordion-header" id="heading_{{ $index }}">
                    <button class="accordion-button {{ $day->active ? '' : 'collapsed' }} {{ $disabled }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $index }}" aria-expanded="{{ $day->active ? 'true' : 'false' }}" aria-controls="collapse_{{ $index }}">
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
                
                <div id="collapse_{{ $index }}" class="accordion-collapse collapse {{ $day->active ? 'show' : '' }}" aria-labelledby="heading_{{ $index }}" data-bs-parent="#accordionDays">
                    <div class="accordion-body">
                        @if (!$disabled)
                            @foreach ($day->activities as $activity)
                                @php
                                    $disabled = $activity->unlocked ? '' : 'disabled';
                                @endphp
                                <div class="card p-2 module mb-2">
                                    <a id="moduleLink_{{ $activity->id }}" class="stretched-link w-100 activity-link {{ $disabled }} pb-1" data-id="{{ $activity->id }}" href="#">
                                        <div class="d-flex">
                                            @if ($activity->completed)
                                                <i class="bi bi-check-square-fill"></i>
                                            @else
                                                <i class="bi bi-square-fill"></i>
                                            @endif
                                            <div>
                                                <p class="activity-font">{{ $activity->title }}</p>
                                                @if ($activity->type)
                                                    <span class="sub-activity-font activity-tag-{{ $activity->type }}">{{ ucfirst($activity->type) }}</span>
                                                @endif
                                                @if ($activity->time)
                                                    <span class="sub-activity-font activity-tag-time">{{ $activity->time.' min' }}</span>
                                                @endif
                                                @if ($activity->optional)
                                                    <span class="sub-activity-font activity-tag-optional"></i>Bonus</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
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
