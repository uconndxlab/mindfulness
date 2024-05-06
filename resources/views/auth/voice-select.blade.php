@extends('layouts.auth')

@section('title', 'Welcome!')

@section('content')
<div>
    Select the voice on this page after account creation
    <a href="{{ route('explore.home') }}">NEXT</a>
</div>
@endsection