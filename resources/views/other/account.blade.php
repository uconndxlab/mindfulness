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
                <small class="{{ $module->completed ? 'fw-bold' : '' }}">{{ $module->daysCompleted }}/{{ $module->totalDays }} days completed</small>
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
