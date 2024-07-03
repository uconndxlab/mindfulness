@extends('layouts.main')

@section('title', 'Home')

@section('content')
<div class="col-md-8">
    <div class="text-left">
        <h1 class="display fw-bold mb-5">Mindfulness Guides</h1>
    </div>

    <div class="">
        @foreach ($weeks as $week)
        <div class="row mb-3 justify-content-center">
            <div class="col-12">
                <div class="h-100">
                    <div class="card p-2 module mb-2">
                        <a id="weekLink" class="stretched-link w-100 {{ $week->order >= 4 ? 'disabled' : ''}}" href="{{ route('explore.week', ['week_id' => $week->id]) }}">{{ $week->name }}</a>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
