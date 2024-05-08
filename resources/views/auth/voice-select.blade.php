@extends('layouts.auth')

@section('title', 'Welcome!')

@section('content')

<div class="col-md-5 border">
    <div class="text-left">
        <h1 class="display font-weight-bold">Voice Selection</h1>
        <p>Please select one of the following voices. This can be changed later on.</p>
    </div>
    <form action="{{ route('user.update.voice') }}" method="POST">
        @method('PUT')
        @csrf
        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="voiceId" id="voice1" value="someName">
                <label class="form-check-label" for="voice1">
                    First Voice
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="voiceId" id="voice2" value="otherName">
                <label class="form-check-label" for="voice2">
                    Second Voice
                </label>
            </div>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">NEXT</button>
        </div>
    </form>
</div>
@endsection