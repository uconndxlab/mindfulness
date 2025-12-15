@extends('layouts.admin')

@section('title', 'Admin | Invitations')

@section('admin_content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Invitations</h1>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Invitation Settings</h5>
                    <button 
                        id="invitation_toggle_btn" 
                        class="btn btn-{{ $invitation_only_mode ? 'primary' : 'warning'}}" 
                        data-route="{{ route('admin.invitations.toggle') }}"
                        {{ $registration_locked ? 'disabled' : '' }}>
                        <i id="invitation_icon" class="bi bi-{{ $invitation_only_mode ? 'envelope-slash' : 'envelope-check'}}"></i>
                        <span id="invitation_text">{{ $invitation_only_mode ? 'Disable Invitation-Only Mode' : 'Enable Invitation-Only Mode'}}</span>
                    </button>
                    @if($registration_locked)
                        <p class="text-muted mt-2 mb-0 small">
                            <i class="bi bi-info-circle"></i> Registration is currently locked. Unlock registration to enable invitation-only mode.
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Send New Invitation</h5>
                    
                    @if ($errors->any() && !$errors->has('email'))
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.invitations.store') }}">
                        @csrf
                        <div class="input-group">
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror" 
                                name="email" 
                                placeholder="Enter email address" 
                                value="{{ old('email') }}"
                                required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-send-fill"></i> Send Invitation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @livewire('invitation-table')
@endsection
