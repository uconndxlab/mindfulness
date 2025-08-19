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
                            @php
                                $disabled = $module->unlocked ? '' : 'disabled';
                            @endphp
                            <a id="moduleLink" class="stretched-link w-100 module-link {{ $disabled }}" {!! $disabled ? '' : 'href='.route('explore.module', ['module_id' => $module->id]) !!}>
                                <img src="{{ Storage::url('flowers/Flower-'. $module->daysCompleted .'.svg') }}" alt="Icon">
                                Part {{ $module->order }} - {{ $module->name }} <br> {{ $module->daysCompleted }}/{{ $module->totalDays }} days completed
                            </a>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
