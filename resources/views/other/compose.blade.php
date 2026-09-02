@extends('layouts.app')

@section('title', $page_info['title'])

@section('content')
<div class="col-md-8">
    <div class="text-left">
        <h1 class="display fw-bold">Journal</h1>
    </div>

    <div id="journalContainer">
        <x-journal :journal="$journal"/>
    </div>
</div>
@endsection
