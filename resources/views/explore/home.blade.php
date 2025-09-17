@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="col-md-8">
    <div class="text-left">
        <h1 class="display fw-bold mb-5">{{ config('app.name') }}</h1>
    </div>

    <div class="">
        @foreach ($modules as $module)
            <div class="row mb-3 justify-content-center">
                <div class="col-12">
                    <div class="h-100">
                        <div class="card p-2 module mb-2">
                            @if ($module->unlocked)
                                <a id="moduleLink" href="{{ route('explore.module', ['module_id' => $module->id]) }}" class="stretched-link w-100 module-link">
                                    <img src="{{ Storage::url('flowers/Flower-'. $module->daysCompleted .'.svg') }}" alt="Icon">
                                    <div class="col">
                                        @php
                                            $completed = $module->totalDays > 0 && $module->daysCompleted == $module->totalDays;
                                            $hasCheckIn = $module->totalCheckInDays > 0;
                                            $completedCheckIn = $module->completedCheckInDays == $module->totalCheckInDays;
                                        @endphp
                                        <h6 class="mb-0">Part {{ $module->order }} - {{ $module->name }}</h6>
                                        <span>
                                            @if ($completed)
                                                <i class="bi bi-check-square-fill me-1"></i>
                                            @endif
                                            {{ $module->daysCompleted }}/{{ $module->totalDays }} days completed
                                            @if ( $hasCheckIn && !$completedCheckIn )
                                                - <i class="bi bi-exclamation-circle text-danger me-1"></i>
                                                {{ $module->completedCheckInDays }}/{{ $module->totalCheckInDays }} Check-Ins complete
                                            @endif
                                        </span>
                                    </div>
                                </a>
                            @else
                                <span id="moduleLink" class="stretched-link w-100 module-link disabled">
                                    <img src="{{ Storage::url('flowers/Flower-'. $module->daysCompleted .'.svg') }}" alt="Icon">
                                    <div class="col">
                                        <h6 class="mb-0">Part {{ $module->order }} - {{ $module->name }}</h6>
                                        <span>
                                            {{ $module->daysCompleted }}/{{ $module->totalDays }} days completed
                                        </span>
                                    </div>
                                </span>
                            @endif
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
