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

    <div class="modal fade" id="lockedActivityModal" tabindex="-1" aria-labelledby="lockedActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lockedActivityModalLabel">Activity Locked</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    This activity is currently locked. Continue progressing to unlock this activity.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
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
                            {{ $day->name }}
                            @if ($day->progress['status'] == 'completed')
                                <i class="bi bi-check2-square"></i>
                            @elseif($disabled)
                                <i class="bi bi-lock"></i>
                            @endif
                        </button>
                    </h2>
                    <div id="collapse_{{ $index }}" class="accordion-collapse collapse {{ $day->progress['show'] ? 'show' : ''}}" aria-labelledby="heading_{{ $index }}" data-bs-parent="#accordionDays">
                        <div class="accordion-body">
                            @if (!$disabled)
                                <!--<p>{{ $day->description }}</p>-->
                                @foreach ($day->activities as $activity)
                                    @php
                                        $title = $activity->optional ? 'OPTIONAL: '.$activity->title : $activity->title;
                                        $activity->status = $day->progress['activity_status'][$activity->id];
                                        $disabled = $activity->status == 'locked' ? 'disabled' : '';
                                    @endphp
                                    <div class="card p-2 module mb-2">
                                        <a id="moduleLink" class="stretched-link w-100 activity-link {{ $disabled }}" data-id="{{ $activity->id }}" data-title="{{ $activity->title }}" href="{{ route('explore.activity', ['activity_id' => $activity->id]) }}">
                                            {!! $activity->status == 'completed' ? '<i class="bi bi-check2-square"></i>' : '' !!}
                                            <span class="activity-font">{{ $title }}</span> <br>
                                            <span class="sub-activity-font">{{ ucfirst($activity->type) }}{{ $activity->time ? ', '.$activity->time.'min' : '' }}</span>
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
                            var myModal = new bootstrap.Modal(document.getElementById('lockedActivityModal'));
                            document.getElementById('lockedActivityModalLabel').innerHTML = 'Locked: ' + activityName;
                            myModal.show();
                        } else {
                            window.location.href = `/explore/activity/${activityId}`;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    });
</script>
@endsection
