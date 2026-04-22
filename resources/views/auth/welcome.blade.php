@extends('layouts.auth')

@section('title', 'Welcome!')

@section('content')
<div class="col-md-6 dark-background">
    <div class="text-left">
        <h1 class="display-3 fs-1 fw-bold mb-1">Welcome!</h1>
        <p class="">Watch this 8 minute video to set up the app on your smartphone and take a tour in the app. After the video, you can get started whenever you are ready. Enjoy!</p>
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