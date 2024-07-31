@extends('layouts.main')

@section('title', 'Account')

@section('content')
<div class="col-md-4">
    @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('user.update.namePass') }}">
        @csrf
        @Method('put')

        <div class="text-center fs-5 fw-bold mt-5 mb-3">
            My Account
        </div>
        
        <div class="form-group mb-3">
            <label class="fw-bold" for="name">Name</label>
            <input id="name" type="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', Auth::user()->name) }}">
            @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
                </span>
                @enderror
        </div>
        
        <div class="form-group mb-3">
            <label class="fw-bold" for="password">New Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
            @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold" for="oldPass">Enter old password to confirm changes:</label>
            <input id="oldPass" type="password" class="form-control @error('oldPass') is-invalid @enderror" name="oldPass">
            @error('oldPass')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="text-center">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">SAVE</button>
            </div>
        </div>
    </form>

    @if (auth::user()->isAdmin())
        <div class="text-center mt-3">
            <div class="form-group">
                <a href="{{ route('module.index') }}" class="btn btn-danger">ADMIN: Content Management</a>
            </div>
        </div>
    @endif

    <div class="text-center fs-5 fw-bold mt-5 mb-3">
        Progress
    </div>
    @foreach ($modules as $module)
        <div class="prior-note">
            <div class="grey-note">
                <h5 class="fw-bold d-flex justify-content-between">
                    <span>{{ $module->name }}</span>
                </h5>
                <small class="{{ $module->progress['status'] == 'completed' ? 'fw-bold' : '' }}">{{ $module->progress['completed'] }}/{{ $module->progress['total'] }} days completed</small>
            </div>
        </div>
    @endforeach
</div>
@endsection
