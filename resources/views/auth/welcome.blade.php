@extends('layouts.auth')

@section('title', 'Welcome!')

@section('content')
<div>
    Welcome page - give introduction video
    <a href="{{ route('voiceSelect') }}">NEXT</a>
</div>
@endsection