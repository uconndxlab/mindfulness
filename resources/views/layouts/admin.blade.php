@extends('layouts.main')

@php
    $layout_type = 'admin';
@endphp

@section('content')
    <div class="container-fluid col-md-3 col-lg-2 p-0 bg-light vh-100 position-fixed">
        <div class="position-sticky pt-3">
            <a class="nav-link btn btn-nav admin-nav-exit" href="{{ route('account') }}">
                <i class="bi bi-arrow-left"></i>
                Exit
            </a>
            <hr>
            <ul class="nav flex-column admin-nav">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" aria-current="page" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house-door me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-people me-2"></i>
                        Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-graph-up me-2"></i>
                        Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">
                        <i class="bi bi-envelope me-2"></i>
                        Email Log?
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 offset-md-3 offset-lg-2">
        @yield('admin_content')
    </div>
@endsection