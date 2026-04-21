@extends('layouts.auth')

@section('title', 'Welcome!')

@section('content')
<div class="col-md-6 dark-background">
    <div class="text-left">
        <h1 class="display-3 fs-1 fw-bold mb-1">Welcome!</h1>
        <p class="">Welcome text goes here. It can wrap on to the following line like this. The following video is just a placeholder. It will be changed.</p>
    </div>
    <div class="text-center">
        <div class="container tutorial-container">
            <x-contentView id="tutorial_video" type="video" file="{{ config('tutorial.video_file') }}" controlsList="noplaybackrate nodownload noseek"/>
        </div>
        <br><br>
        <a href="{{ route('explore.home') }}" class="btn btn-primary">NEXT</a>
    </div>
</div>
@endsection