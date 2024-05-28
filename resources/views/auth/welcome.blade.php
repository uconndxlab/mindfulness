@extends('layouts.auth')

@section('title', 'Welcome!')

@section('content')
<div class="col-md-5 border">
    <div class="text-left">
        <h1 class="display-3 font-weight-bold">Welcome!</h1>
        <p class="lead">Welcome text goes here. It can wrap on to the following line like this. The following video is just a placeholder. It will be changed.</p>
    </div>
    <div class="text-center">
        <x-contentView id="welcome_video" type="video" file="videoExampleSnarky.MOV"/>
        <br><br>
        <a href="{{ route('voiceSelect') }}" class="btn btn-primary btn-lg">NEXT</a>
    </div>
</div>
@endsection