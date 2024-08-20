@extends('layouts.main')

@section('title', $module->name)

@section('content')
<div class="col-md-8">
    <div class="text-left mb-3">
        <h1 class="display fw-bold mb-1">{{ $module->name }}</h1>
        <p>{{ $module->description }}</p>
        @if ($module->workbook_path)
            <x-contentView type="pdf" file="{{ $module->workbook_path }}"/>
        @endif
    </div>

    <div class="">
        <h4 class="mb-2">Sessions</h4>
        <div class="accordion accordion-flush mb-3" id="accordionDays">
            @foreach ($module->days as $index => $day)
                @php
                    $disabled = $day->progress['status'] == 'locked' ? 'disabled' : '';
                @endphp

                <div class="accordion-item border mb-2">
                    <h2 class="accordion-header" id="heading_{{ $index }}">
                        <button class="accordion-button {{ $day->progress['show'] ? '' : 'collapsed'}}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $index }}" aria-expanded="false" aria-controls="collapse_{{ $index }}" {{ $disabled }}>
                            <div>
                                {{ $day->name }}
                                
                                @if ($day->progress['status'] == 'completed')
                                    <i class="bi bi-check2-square"></i>
                                @elseif($disabled)
                                    <i class="bi bi-lock"></i>
                                @endif

                                <br>
                                <span style="font-weight:400;">{{ $day->description }}</span>
                            </div>
                        </button>
                    </h2>
                    
                    <div id="collapse_{{ $index }}" class="accordion-collapse collapse {{ $day->progress['show'] ? 'show' : ''}}" aria-labelledby="heading_{{ $index }}" data-bs-parent="#accordionDays">
                        <div class="accordion-body">
                            @if (!$disabled)
                                @foreach ($day->activities as $activity)
                                    @php
                                        $activity->status = $day->progress['activity_status'][$activity->id];
                                        $disabled = $activity->status == 'locked' ? 'disabled' : '';
                                    @endphp
                                    <div class="card p-2 module mb-2">
                                        <a id="moduleLink" style="padding-bottom:10px;" class="stretched-link w-100 activity-link {{ $disabled }}" data-id="{{ $activity->id }}" data-title="{{ $activity->title }}" href="{{ route('explore.activity', ['activity_id' => $activity->id]) }}">
                                            
                                            <div style="display:flex;"><p class="activity-font">{{ $activity->title }}</p> {!! $activity->status == 'completed' ? '<i style="font-size:16px;margin-left:5px;" class="bi bi-check2-square"></i>' : '' !!}</div>
                                            @if ($activity->type)
                                                <span class="sub-activity-font activity-tag-activity">{{ ucfirst($activity->type) }}</span>
                                            @endif
                                            @if ($activity->time)
                                                <span class="sub-activity-font activity-tag-time"><i class="bi bi-clock"></i>{{ $activity->time.' min' }}</span>
                                            @endif
                                            <!-- TODO -->
                                            @if ($activity->optional)
                                                <span class="sub-activity-font activity-tag-optional"></i>Optional</span>
                                            @endif
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
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.activity-link').forEach(function (activity) {
            activity.addEventListener('click', function (event) {
                event.preventDefault();
                var activityId = this.getAttribute('data-id');
                var activityName = this.getAttribute('data-title');

                fetch(`/checkActivity/${activityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.locked) {
                            showModal('Locked: '+activityName, 'This activity is currently locked. Continue progressing to unlock this activity.');
                        } else {
                            window.location.href = `/explore/activity/${activityId}`;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

        //disabling the accordion buttons
        const accordionButtons = document.querySelectorAll('.accordion-button.disabled');
        accordionButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
            });
        });
    });
</script>
@endsection
