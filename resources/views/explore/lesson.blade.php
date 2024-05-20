@extends('layouts.main')

@section('title', $lesson->title)

@section('content')
<div class="col-md-8">
    <div class="text-left">
        <h1 class="display font-weight-bold">{{ $lesson->title }}</h1>
    </div>

    <div class="container manual-margin-top">
            Content
    </div>

</div>
@endsection