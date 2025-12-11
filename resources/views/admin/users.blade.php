@extends('layouts.admin')

@section('title', 'Admin | Users')

@section('admin_content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Users</h1>
    </div>

    @livewire('user-table')
@endsection 