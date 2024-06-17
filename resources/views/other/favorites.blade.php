@extends('layouts.main')

@section('title', 'Meditation Library')

@section('content')
<div class="col-md-8">
    @php
    @endphp

    <div class="text-left">
        <h1 class="display fw-bold">Favorites</h1>
    </div>

    @if ($favorites->isEmpty()) 
        <div class="text-left muted">
            <span>Click the "<i class="bi bi-star"></i>" on lessons add them to your favorites and view them here!</span> 
        </div>
    @else
        <div class="">
            <div class="row mb-3 justify-content-center">
                <div class="col-12">
                    <div class=" h-100">
                        @foreach ($favorites as $favorite)
                            <div class="card module p-2 mb-2">
                                <a class=" stretched-link w-100" href="{{ route('explore.lesson', ['lessonId' => $favorite->lesson->id, 'from' => 'fav']) }}">
                                    {{ $favorite->lesson->module->name }} - {{ $favorite->lesson->title }}
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
