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
    <div class="accordion border accordion-flush mb-3" id="accordionDays">
            @foreach ($module->days as $index => $day)
                @php
                    $disabled = $day_progress < $day->order ? 'disabled' : '';
                @endphp

                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading_{{ $index }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $index }}" aria-expanded="false" aria-controls="collapse_{{ $index }}" {{ $disabled }}>
                            {{ $day->name }}
                            @if ($disabled)
                                <i class="bi bi-lock"></i>
                            @endif
                        </button>
                    </h2>
                    <div id="collapse_{{ $index }}" class="accordion-collapse collapse {{ $day->show ? 'show' : ''}}" aria-labelledby="heading_{{ $index }}" data-bs-parent="#accordionDays">
                        <div class="accordion-body">
                            @if (!$disabled)
                                <p>{{ $day->description }}</p>
                                @foreach ($day->activities as $activity)
                                    @php
                                        $locked = $activity_progress < $activity->order ? 'disabled' : '';
                                        $title = $activity->optional ? 'OPTIONAL: '.$activity->title : $activity->title
                                    @endphp
                                    @if ($locked)
                                        <div class="card p-2 module mb-2">
                                            <a id="moduleLink" class="stretched-link w-100 disabled" disabled>{{ $title }}</a>
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    @else
                                        <div class="card p-2 module mb-2">
                                            <a id="moduleLink" class="stretched-link w-100" href="{{ route('explore.activity', ['activity_id' => $activity->id]) }}">{{ $title }}</a>
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    @endif
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
