@extends('layouts.main')

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
                                $status = $module->progress['status'];
                                $disabled = $module->progress['status'] == 'locked' ? 'disabled' : '';
                            @endphp
                            <a style="display:flex" id="moduleLink" class="stretched-link w-100 {{ $disabled }}" {!! !$disabled ? 'href='.route('explore.module', ['module_id' => $module->id]) : '' !!}>
                                <img src="{{ Storage::url('content/Flower-'.$module->progress['completed'].'.svg') }}" alt="Icon" style="width:50px; height:50px; margin-right:10px;">
                                {{ $module->name }} <br> {{$module->progress['completed']}}/{{$module->progress['total']}} sessions completed
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
