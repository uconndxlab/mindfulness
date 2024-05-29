@extends('layouts.main')

@section('title', 'Home')

@section('content')
<div class="col-8">
    @php
        $adminCheck = isset($fromAdmin) && $fromAdmin && Auth::user()->isAdmin();
        $route = $adminCheck ? 'admin.lesson.show' : 'explore.lesson';
        $header = $adminCheck ? '***EDIT MODULES:***' : 'Mindfulness Modules:';
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

    <div class="container">
        @foreach ($modules as $module)
        <div class="row mb-3 border justify-content-center">
            <div class="col-5">
                <div class="p-2 bg-secondary h-100">
                    <p>{{ $module->name }}:</p>
                    @foreach ($module->lessons as $lesson)
                        <div class="p-1">
                            <a class="btn btn-primary w-100 {{ $progress < $lesson->order ? 'disabled' : ''}}" href="{{ route($route, ['lessonId' => $lesson->id]) }}">{{ $lesson->title }}</a>
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
