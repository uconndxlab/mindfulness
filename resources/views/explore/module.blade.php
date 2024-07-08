@extends('layouts.main')

@section('title', $module->name)

@section('content')
<div class="col-md-8">
    <div class="text-left mb-3">
        <h1 class="display fw-bold mb-1">{{ $module->name }}</h1>
        <p>{{ $module->description }}</p>
    </div>

    <div class="">
    <div class="accordion border accordion-flush mb-3" id="accordianDays">
            @foreach ($module->days as $index => $day)
                @php
                    $disabled = $day->order >= 4 ? 'disabled' : ''
                @endphp
                <div class="accordion-item {{ $disabled }}">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="{{ $disabled ? '' : 'collapse' }}" data-bs-target="#collapse_{{ $index }}" aria-expanded="false" aria-controls="collapse_{{ $index }}" {{ $disabled }}>
                            {{ $day->name }}
                            @if ($disabled)
                                <i class="bi bi-lock"></i>
                            @endif
                        </button>
                    </h2>
                    <div id="collapse_{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading_{{ $index }}" data-bs-parent="#accordionDays">
                        <div class="accordion-body">
                            @if (!$disabled)
                                <p>{{ $day->description }}</p>
                                @foreach ($day->activities as $activity)
                                    <div class="card p-2 module mb-2">
                                        <a id="moduleLink" class="stretched-link w-100" href="{{ route('explore.activity', ['activity_id' => $activity->id]) }}">{{ $activity->title }}</a>
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
@endsection
