@if (!isset($activities) || $activities->isEmpty())
    @if (isset($random))
        <div class="text-left muted">
            No matches found. Here is a random activity you might like to practice:
        </div>
        <div class="card module p-2 mb-2">
            <a class="stretched-link w-100" href="{{ route('explore.activity', ['activity_id' => $random->id, 'library' => true]) }}">
                <span class="activity-font">{{ $random->title }} - {{ $random->sub_header }}</span> <br>
                <span class="sub-activity-font">{{ $random->day->module->name }}, {{ $random->day->name }}{{ $random->optional ? ' - Optional' : '' }}</span>
            </a>
            <i class="bi bi-arrow-right"></i>
        </div>
    @elseif (isset($empty_text))
        <div class="text-left muted">
            {!! $empty_text !!}
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
