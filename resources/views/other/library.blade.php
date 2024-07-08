@extends('layouts.main')

@section('title', $page_info['title'])

@section('content')
<div class="col-md-8">
    @php
        $route_name = Request::route()->getName();
        $library = [false, false];
        if ($route_name == 'library.meditation') {
            $library[0] = true;
        }
        else {
            $library[1] = true;
        }
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid container">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link {{ $library[0] ? 'active disabled' : ''}}" href="{{ $library[0] ? '' : route('library.meditation') }}">Meditation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $library[1] ? 'active disabled' : ''}}" href="{{ $library[1] ? '' : route('library.favorites') }}">Favorites</a>
                </li>
            </ul>
        </div>
    </nav>


    <div class="text-left">
        <h1 class="display fw-bold">{{ $page_info['title'] }}</h1>
    </div>

    @if ($activities->isEmpty()) 
        <div class="text-left muted">
            {!! $page_info['empty'] !!}
        </div>
    @else
        <div class="">
            <div class="row mb-3 justify-content-center">
                <div class="col-12">
                    <div class=" h-100">
                        @foreach ($activities as $activity)
                            <div class="card module p-2 mb-2">
                                <a class=" stretched-link w-100" href="{{ route('explore.activity', ['activity_id' => $activity->id]) }}">
                                    {{ $activity->day->module->name }}, {{ $activity->day->name }} - {{ $activity->title }}
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
