@extends('layouts.app')

@section('title', 'Account')

@section('content')
<div class="col-md-8">
    <h4 class="text-center fw-bold mb-3">
        Progress
    </h4>
    @foreach ($modules as $module)
        <div class="prior-note">
            <div class="grey-note">
                <h5 class="fw-bold d-flex justify-content-between">
                    <span>Part {{ $module->order }} - {{ $module->name }}</span>
                </h5>
                <span>
                    @if($module->totalDays > 0)
                        <small class="{{ $module->daysCompleted == $module->totalDays ? 'fw-bold' : '' }}">
                            {{ $module->daysCompleted }}/{{ $module->totalDays }} Days
                        </small>
                    @endif
                    @if($module->totalCheckInActivities > 0)
                        <small class="{{ $module->completedCheckInActivities == $module->totalCheckInActivities ? 'fw-bold' : '' }}">
                            , {{ $module->completedCheckInActivities }}/{{ $module->totalCheckInActivities }} Quick Check-Ins
                        </small>
                    @endif
                    @if($module->totalCheckInDays > 0)
                        <small class="{{ $module->completedCheckInDays == $module->totalCheckInDays ? 'fw-bold' : '' }}">
                            , {{ $module->completedCheckInDays }}/{{ $module->totalCheckInDays }} Rate My Awareness
                        </small>
                    @endif
                </span>
            </div>
        </div>
    @endforeach

    <h4 class="text-center fw-bold mt-5 mb-3">My Account</h4>
    @livewire('account-update-form')
    
    @if (Auth::user()->isAdmin())
        <div class="text-center mt-3">
            <div class="form-group">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">ADMIN DASHBOARD</a>
            </div>
        </div>
    @endif
</div>
@endsection
