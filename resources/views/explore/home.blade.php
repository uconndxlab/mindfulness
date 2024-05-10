@extends('layouts.main')

@section('title', 'Home')

@section('content')
<div class="col-md-8">
    <div class="text-left">
        <h1 class="display font-weight-bold">Mindfulness Modules:</h1>
    </div>

    <div class="container">
        @foreach ($modules as $module)
        <div class="row mb-3 border justify-content-center d-flex align-items-stretch">
            <div class="col-6">
                <div class="p-2 bg-secondary d-flex flex-column h-100">
                    <p>{{ $module->name }}:</p>
                    @foreach ($module->lessons as $lesson)
                        <div class="p-1">
                            <a class="btn btn-primary btn-block" href="{{ route('explore.lesson', ['contentKey' => $lesson->id]) }}">{{ $lesson->title }}</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
