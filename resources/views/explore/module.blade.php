@extends('layouts.main')

@section('title', $module->name)

@section('content')
<div class="col-md-8">
    <div class="text-left mb-3">
        <h1 class="display fw-bold mb-1">{{ $module->name }}</h1>
        <p>{{ $module->description }}</p>
        @if ($module->workbook_path)
            <x-pdf-viewer fpath="{{ Storage::url('content/'.$module->workbook_path) }}" wbName="{{ $module->name }}" />
        @endif
    </div>

    <div class="">
        <h5 class="mb-2">Progress: {{ $module->progress_days[0] }}/{{ $module->progress_days[1] }} days completed</h5>
        <div class="accordion accordion-flush mb-3" id="accordionDays">
            @foreach ($module->days as $index => $day)
                @php
                    $disabled = $day->progress['status'] == 'locked' ? 'disabled' : '';
                    $show = false;
                    if ($override_accordion) {
                        $show = $override_accordion === 'day_'.$day->id;
                    }
                    else {
                        $show = $day->progress['show'];
                    }
                @endphp

                <div class="accordion-item border mb-2" id="day_{{ $day->id }}">
                    <h2 class="accordion-header" id="heading_{{ $index }}">
                        <button class="accordion-button {{ $show ? '' : 'collapsed' }} {{ $disabled }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $index }}" aria-expanded="{{ $show ? 'true' : 'false' }}" aria-controls="collapse_{{ $index }}">
                            <div class="d-flex w-100">                                
                                @if ($day->progress['status'] == 'completed')
                                    <i class="bi bi-check-square-fill"></i>
                                @elseif($disabled)
                                    <i class="bi bi-lock-fill"></i>
                                @else
                                    <i style="visibility:hidden;" class="bi bi-square-fill"></i>
                                @endif
                                <div class="flex-grow-1 pe-4">
                                    <div class="text-dark fw-bold">{{ $day->name }}</div>
                                    <div class="text-dark fw-normal">{{ $day->description }}</div>
                                </div>
                                <div class="d-flex align-items-center gap-2" style="min-width: 60px">
                                </div>
                            </div>
                        </button>
                    </h2>
                    
                    <div id="collapse_{{ $index }}" class="accordion-collapse collapse {{ $show ? 'show' : '' }}" aria-labelledby="heading_{{ $index }}" data-bs-parent="#accordionDays">
                        <div class="accordion-body">
                            @if (!$disabled)
                                @foreach ($day->activities as $activity)
                                    @php
                                        $activity->status = $day->progress['activity_status'][$activity->id];
                                        $disabled = $activity->status == 'locked' ? 'disabled' : '';
                                    @endphp
                                    <div class="card p-2 module mb-2">
                                        <a id="moduleLink" style="padding-bottom:10px;" class="stretched-link w-100 activity-link {{ $disabled }}" data-id="{{ $activity->id }}" href="#">
                                            
                                            <div style="display:flex;">
                                                @if ($activity->status == 'completed')
                                                    <i style="font-size:16px;" class="bi bi-check-square-fill"></i>
                                                @else
                                                    <i style="font-size:16px; visibility:hidden;" class="bi bi-square-fill"></i>
                                                @endif
                                            <div><p class="activity-font">{{ $activity->title }}</p>
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
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        //scroll to bonus activity
        var dayId = @json($override_accordion);
        if (dayId) {
            console.log('override found');
            var dayElement = document.getElementById(dayId);
            if (dayElement) {
                setTimeout(function() {
                    var bonusActivity = dayElement.querySelector('.activity-tag-optional');
                    if (bonusActivity) {
                        var offset = 125;
                        var elementPosition = bonusActivity.getBoundingClientRect().top;
                        var offsetPosition = elementPosition + window.pageYOffset - offset;
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                }, 100); //adding delay so that accordion can open
            }
        }

        document.querySelectorAll('.activity-link').forEach(function (activity) {
            activity.addEventListener('click', function (event) {
                event.preventDefault();
                var activityId = this.getAttribute('data-id');

                return new Promise((resolve, reject) => {
                    axios.get(`/checkActivity/${activityId}`)
                        .then(response => {
                            if (response.data.locked) {
                                showModal(response.data.modalContent);
                            } else {
                                window.location.href = `/explore/activity/${activityId}`;
                            }
                            resolve(true);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            reject(false);
                        });
                });
            });
        });
    });
</script>
@endsection
