@extends('layouts.main')

@section('title', 'Home')

@section('content')
<div>
    EXPLORE PAGE - I (MAIN)
    <a href="{{ route('explore.weekly') }}">Go to EXPLORE - II</a>
</div>
@endsection
