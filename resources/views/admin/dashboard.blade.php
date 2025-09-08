@extends('layouts.admin')

@section('admin_content')
    <h1>Dashboard</h1>
    <div class="row">
        <button id="lock_button_reg" class="btn btn-{{ $registration_locked ? 'primary' : 'danger'}}" data-route="{{ route('admin.lock-registration-access') }}">
            <i id="lock_icon_reg" class="bi bi-{{ $registration_locked ? 'unlock' : 'lock'}}"></i>
            <span id="lock_text_reg">{{ $registration_locked ? 'UNLOCK REGISTRATION' : 'Lock Registration'}}</span>
        </button>
    </div>
@endsection
