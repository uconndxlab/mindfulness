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
        <div class="container">
            <div class="row mb-3 border justify-content-center">
                <div class="col-5">
                    <div class="p-2 bg-secondary h-100">
                        @foreach ($favorites as $favorite)
                            <div class="p-1">
                                <a class="btn btn-primary w-100" href="{{ route('explore.lesson', ['lessonId' => $favorite->lesson->id]) }}">
                                    {{ $favorite->lesson->module->name }} - {{ $favorite->lesson->title }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
