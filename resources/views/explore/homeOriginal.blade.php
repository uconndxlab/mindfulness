@extends('layouts.main')

@section('title', 'Home')

@section('content')
<div class="col-md-8">
    @php
        $adminCheck = isset($fromAdmin) && $fromAdmin && Auth::user()->isAdmin();
        $route = $adminCheck ? 'admin.lesson.show' : 'explore.lesson';
        $header = $adminCheck ? '***EDIT MODULES:***' : 'Mindfulness Guides';
        $progress = Auth::user()->progress;
    @endphp

    <div class="text-left">
        <h1 class="display fw-bold">{{ $header }}</h1>
    </div>

    @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <div class="">
        @foreach ($modules as $module)
        <div class="row mb-3 justify-content-center">
            <div class="col-12">
                <div class="h-100">
                    <h5 class="fw-bold">{{ $module->name }}:</h5>
                    <p></p>
                    @foreach ($module->lessons as $lesson)
                        <div class="card p-2 module mb-2">
                            <a class="stretched-link w-100 {{ $progress < $lesson->order ? 'disabled' : ''}}" href="{{ route($route, ['lessonId' => $lesson->id]) }}">{{ $lesson->title }}</a>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    @endforeach
                    @if ($adminCheck)
                        <div class="p-1">
                            <a class="btn btn-success w-100" href="{{ route('admin.lesson.create', ['moduleId' => $module->id]) }}">+</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
