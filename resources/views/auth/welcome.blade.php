@extends('layouts.auth')

@section('title', 'Welcome!')

@section('content')
<div class="col-md-5 border">
    <div class="text-left">
        <h1 class="display-3 font-weight-bold">Welcome!</h1>
        <p class="lead">Welcome text goes here. It can wrap on to the following line like this.</p>
    </div>
    <div class="text-center">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/LtNYaH61dXY?si=RyyPvLtKQyNmZPSE&amp;controls=0" allowfullscreen></iframe>
        </div>
        <br><br>
        <a href="{{ route('voiceSelect') }}" class="btn btn-primary btn-lg">NEXT</a>
    </div>
</div>
@endsection