@extends('layouts.main')

@section('title', $page_info['title'])

@section('content')
<div class="col-md-8">
    @php
    @endphp

    <div class="text-left">
        <h1 class="display fw-bold">{{ $page_info['title'] }}</h1>
    </div>

    @if ($activities->isEmpty()) 
        <div class="text-left muted">
            {{ $page_info['empty'] }}
        </div>
    @else
        <div class="">
            <div class="row mb-3 justify-content-center">
                <div class="col-12">
                    <div class=" h-100">
                        @foreach ($activities as $activity)
                            <div class="card module p-2 mb-2">
                                <a class=" stretched-link w-100" href="{{ route('explore.activity', ['activity_id' => $activity->id]) }}">
                                    {{ $activity->day->week->name }}, {{ $activity->day->name }} - {{ $activity->title }}
                                </a>
                                <i class="bi bi-arrow-right"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
