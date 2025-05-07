<!DOCTYPE html>
<html lang="en">
    @php
        $is_app = isset($layout_type) && $layout_type === 'app';
    @endphp
    <head>
        @include('layouts.partials.head')
        <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    </head>
    <body class="{{ !$is_app ? 'd-flex align-items-center py-4 bg-body-tertiary' : '' }}">
        @if($is_app)
            @include('layouts.partials.navigation')
        @endif

        <main class="container @if($is_app) manual-margins @endif">
            <div class="row justify-content-center"> 
                @yield('content')
            </div>
        </main>

        @if($is_app)
            @include('layouts.partials.modal')
        @endif

        <!-- scripts -->
        <script src="{{ asset('js/main.js') }}"></script>
        @if($is_app)
            <script src="{{ asset('js/modal.js') }}"></script>
            @if(session('modal_data'))
                <script>
                    window.sessionModalData = @json(session('modal_data'));
                    {{ session()->forget('modal_data') }}
                </script>
            @endif
        @endif
        @yield('additional_scripts')
    </body>
</html> 