@php
    $rand_act = isset($random) ? $random : null;
@endphp
@if (!isset($activities) || $activities->isEmpty())
    <div class="text-left muted">
        No matches found. {{ $rand_act ? 'Here is a random activity you might like to practice: ' : ''}}
    </div>
    @if ($rand_act)
        <div class="card module p-2 mb-2">
            <a class="stretched-link w-100" href="{{ route('explore.activity', ['activity_id' => $rand_act->id, 'library' => true]) }}">
                <span class="activity-font">{{ $rand_act->title }} - {{ $rand_act->sub_header }}</span> <br>
                <span class="sub-activity-font">{{ $rand_act->day->module->name }}, {{ $rand_act->day->name }}{{ $rand_act->optional ? ' - Optional' : '' }}</span>
            </a>
            <i class="bi bi-arrow-right"></i>
        </div>
    @endif
@else
    <div class="row mb-3 justify-content-center">
        <div class="col-12">
            <div class=" h-100">
                @foreach ($activities as $activity)
                    <div class="card module p-2 mb-2">
                        <a class="stretched-link w-100" href="{{ route('explore.activity', ['activity_id' => $activity->id, 'library' => true]) }}">
                            <span class="activity-font">{{ $activity->title }} - {{ $activity->sub_header }}</span> <br>
                            <span class="sub-activity-font">{{ $activity->day->module->name }}, {{ $activity->day->name }}{{ $activity->optional ? ' - Optional' : '' }}</span>
                        </a>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div>
        {{ $activities->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endif
