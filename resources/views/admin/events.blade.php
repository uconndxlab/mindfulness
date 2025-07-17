@extends('layouts.admin')

@section('admin_content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Event Log</h1>
    </div>

    @livewire('event-log-table')
@endsection 