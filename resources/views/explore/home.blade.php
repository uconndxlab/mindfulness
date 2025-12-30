@extends('layouts.app')

@section('title', 'Home')
@section('page_id', 'home')

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
                            @if ($module->unlocked)
                                <a id="moduleLink" href="{{ route('explore.module', ['module_id' => $module->id]) }}" class="stretched-link w-100 module-link">
                                    <img src="{{ Storage::url('flowers/Flower-'. $module->daysCompleted .'.svg') }}" alt="Icon">
                                    <div class="col">
                                        <h6 class="mb-0">Part {{ $module->order }} - {{ $module->name }}</h6>
                                        <ul class="text-muted ps-2 mb-0">
                                            <li class="list-check{{ $module->daysCompleted == $module->totalDays ? '-filled' : '' }}">{{ $module->daysCompleted }}/{{ $module->totalDays }} Days</li>
                                            @if ($module->totalCheckInActivities > 0)
                                                <li class="list-check{{ $module->completedCheckInActivities == $module->totalCheckInActivities ? '-filled' : '' }}">{{ $module->completedCheckInActivities }}/{{ $module->totalCheckInActivities }} Quick Check-Ins</li>
                                            @endif
                                            @if ($module->totalCheckInDays > 0)
                                                <li class="list-check{{ $module->completedCheckInDays == $module->totalCheckInDays ? '-filled' : '' }}">{{ $module->completedCheckInDays }}/{{ $module->totalCheckInDays }} Rate My Awareness</li>
                                            @endif
                                        </ul>
                                    </div>
                                </a>
                            @else
                                <a href="#" class="stretched-link w-100 module-link disabled locked-module-link" data-module-name="Part {{ $module->order }} - {{ $module->name }}">
                                    <img src="{{ Storage::url('flowers/Flower-'. $module->daysCompleted .'.svg') }}" alt="Icon">
                                    <div class="col">
                                        <h6 class="mb-0">Part {{ $module->order }} - {{ $module->name }}</h6>
                                        <ul class="text-muted ps-2 mb-0">
                                            <li class="list-check">{{ $module->daysCompleted }}/{{ $module->totalDays }} Days</li>
                                            @if ($module->totalCheckInActivities > 0)
                                                <li class="list-check{{ $module->completedCheckInActivities == $module->totalCheckInActivities ? '-filled' : '' }}">{{ $module->completedCheckInActivities }}/{{ $module->totalCheckInActivities }} Quick Check-Ins</li>
                                            @endif
                                            @if ($module->totalCheckInDays > 0)
                                                <li class="list-check{{ $module->completedCheckInDays == $module->totalCheckInDays ? '-filled' : '' }}">{{ $module->completedCheckInDays }}/{{ $module->totalCheckInDays }} Rate My Awareness</li>
                                            @endif
                                        </ul>
                                    </div>
                                </a>
                            @endif
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @if ($bonusInfo['numberBonusUnlocked'] > 0)
            <hr>
            <div class="row mb-3 justify-content-center">
                <div class="col-12">
                    <div class="h-100">
                        <div class="card p-2 module mb-2">
                            <a id="bonusLink" href="{{ route('explore.bonus') }}" class="stretched-link w-100 module-link">
                                @php
                                    $petals = floor($bonusInfo['numberBonusCompleted'] / $bonusInfo['totalBonus'] * 5);
                                @endphp
                                <img src="{{ Storage::url('flowers/Flower-'.$petals.'.svg') }}" alt="Icon">
                                <div class="col">
                                    <h6 class="mb-0">Bonus Activities</h6>
                                    <ul class="text-muted ps-2 mb-0">
                                        <li class="list-check{{ $bonusInfo['numberBonusUnlocked'] == $bonusInfo['totalBonus'] ? '-filled' : '' }}">{{ $bonusInfo['numberBonusUnlocked'] }}/{{ $bonusInfo['totalBonus'] }} Activities Unlocked</li>
                                        <li class="list-check{{ $bonusInfo['numberBonusCompleted'] == $bonusInfo['totalBonus'] ? '-filled' : '' }}">{{ $bonusInfo['numberBonusCompleted'] }}/{{ $bonusInfo['totalBonus'] }} Activities Completed</li>
                                    </ul>
                                </div>
                            </a>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
