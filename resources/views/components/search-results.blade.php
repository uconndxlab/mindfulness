@if (!isset($activities) || $activities->isEmpty())
    @if (isset($random))
        <div class="text-left muted">
            No matches found. Here is an activity you might like to practice:
        </div>
        <div class="card module p-2 mb-2">
            <a class="stretched-link w-100" href="{{ route('explore.activity', ['activity_id' => $random->id, 'library' => true]) }}">
                <span class="activity-font">{{ $random->title }}</span> <br>
                <span class="sub-activity-font">{{ ucfirst($random->type) }}{{ $random->time ? ', '.$random->time.'min' : '' }}{{ $random->optional ? ', Optional' : '' }}</span>
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
                            <p class="activity-font"style="margin-bottom:0px!important;">{{ $activity->title }}</p> 
                            <p class="sub-activity-font">{{ $activity->day->name.', '.$activity->day->module->name}}</p>
                            @if ($activity->type)
                                <span class="sub-activity-font activity-tag-activity">{{ ucfirst($activity->type) }}</span>
                            @endif
                            @if ($activity->time)
                                <span class="sub-activity-font activity-tag-time"><i class="bi bi-clock"></i>{{ $activity->time.' min' }}</span>
                            @endif
                            @if ($activity->optional)
                                <span class="sub-activity-font activity-tag-optional"></i>Optional</span>
                            @endif
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
