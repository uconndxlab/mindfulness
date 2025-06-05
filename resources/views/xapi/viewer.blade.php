@extends('layouts.app') 

@section('content')
    <div class="container-fluid">
        <h2>{{ $package->title }}</h2>
        <div style="width: 100%; height: 85vh; border: 1px solid #ccc;">
            <iframe src="{{ $iframeSrc }}" 
                    style="width: 100%; height: 100%; border: none;"
                    allowfullscreen="allowfullscreen" 
                    allow="autoplay *; fullscreen *">
            </iframe>
        </div>
    </div>
@endsection