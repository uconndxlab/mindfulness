@extends('layouts.auth')

@section('title', 'Welcome!')

@section('content')
<div class="col-md-5">
    <div class="text-left">
        <h1 class="display-3 font-weight-bold">Welcome!</h1>
        <p class="lead">Welcome text goes here. It can wrap all the way to the next line.</p>
    </div>
    <div class="text-center">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/LtNYaH61dXY?si=RyyPvLtKQyNmZPSE&amp;controls=0" allowfullscreen></iframe>
        </div>
        <br><br>
        <a href="{{ route('voiceSelect') }}" class="btn btn-primary btn-lg">Next</a>
    </div>
</div>
@endsection