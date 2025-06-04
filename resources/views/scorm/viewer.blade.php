@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ $package->title }}</div>

                <div class="card-body p-0">
                    <iframe id="scorm-content" 
                            src="{{ $entryUrl }}" 
                            style="width: 100%; height: 80vh; border: none;"
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        @if ($packageType === 'scorm')
            // Initialize SCORM API for SCORM packages
            const API = new ScormApiWrapper('{{ $package->id }}');
            window.API = API;
            window.API_1484_11 = API; // For SCORM 2004 support
        @else
            // For xAPI, no client-side SCORM API wrapper is needed from our app.
            // The content itself will handle xAPI communication using the launch parameters.
            console.log('Launching xAPI package. Entry URL:', '{{ $entryUrl }}');
            @if ($xapiLaunchParams)
                console.log('xAPI Launch Parameters:', @json($xapiLaunchParams));
            @endif
        @endif
    </script>
    @endpush
@endsection 