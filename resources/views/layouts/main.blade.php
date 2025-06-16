<!DOCTYPE html>
<html lang="en">
    @php
        $type = isset($layout_type) ? $layout_type : 'auth';
    @endphp
    <head>
        @include('layouts.partials.head')
    </head>
    <body class="{{ $type === 'auth'? 'd-flex align-items-center py-4 bg-body-tertiary' : '' }}">
        @if($type === 'app')
            @include('layouts.partials.navigation')
        @endif

        <main class="container{{ $type !== 'auth' ? '-fluid' : '' }} {{ $type === 'app' ? 'manual-margins' : '' }}">
            <div class="row @if($type !== 'admin') justify-content-center @endif"> 
                @yield('content')
            </div>
        </main>

        @if($type === 'app' || $type === 'admin')
            @include('layouts.partials.modal')
        @endif
        @include('layouts.partials.scripts')
        @yield('additional_scripts')
    </body>
</html> 