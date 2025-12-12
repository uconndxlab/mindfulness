@extends('layouts.main')

@php
    $layout_type = 'admin';
@endphp

@section('content')
    <div id="admin-sidebar" class="bg-light">
        <div class="position-sticky pt-3">
            <div class="d-flex justify-content-between align-items-center px-3">
                <a class="nav-link btn btn-nav admin-nav-exit" href="{{ route('account') }}">
                    <i class="bi bi-arrow-left"></i>
                    Exit
                </a>
                <button class="btn btn-light admin-nav-exit d-md-none" id="sidebar-close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <hr>
            <ul class="nav flex-column admin-nav">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" aria-current="page" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house-door me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                        <i class="bi bi-people me-2"></i>
                        Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.invitations*') ? 'active' : '' }}" href="{{ route('admin.invitations') }}">
                        <i class="bi bi-envelope-paper me-2"></i>
                        Invitations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.events') ? 'active' : '' }}" href="{{ route('admin.events') }}">
                        <i class="bi bi-list-check me-2"></i>
                        Event Log
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reflection') ? 'active' : '' }}" href="{{ route('admin.reflection') }}">
                        <i class="bi bi-patch-question me-2"></i>
                        Reflections
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.journals') ? 'active' : '' }}" href="{{ route('admin.journals') }}">
                        <i class="bi bi-journal-plus me-2"></i>
                        Journals
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div id="main-content">
        <header class="d-md-none d-flex justify-content-between align-items-start p-2 border-bottom admin-mobile-header">
            <button class="btn btn-light admin-nav-exit" id="sidebar-open">
                <i class="bi bi-list"></i>
            </button>
        </header>
        <div class="admin-content-wrapper">
             @yield('admin_content')
        </div>
    </div>
@endsection