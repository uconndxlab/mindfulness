@extends('layouts.admin')

@section('title', 'Admin | Journals')

@section('admin_content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Journals</h1>
    </div>

    @livewire('note-table')
@endsection 

