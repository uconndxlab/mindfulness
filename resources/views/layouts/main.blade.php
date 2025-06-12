<!DOCTYPE html>
<html lang="en">
    @php
        $is_app = isset($layout_type) && $layout_type === 'app';
    @endphp
    <head>
        @include('layouts.partials.head')
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

        @include('layouts.partials.scripts')

        @yield('additional_scripts')
    </body>
</html> 