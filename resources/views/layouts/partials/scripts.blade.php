<!-- scripts -->
@livewireScripts

@php
    $page_type = isset($layout_type) ? $layout_type : 'auth';
@endphp

@if($page_type === 'app' || $page_type === 'admin')
    <script src="{{ asset('js/modal.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
@endif

@if(session('modal_data'))
    <script>
        window.sessionModalData = @json(session('modal_data'));
        {{ session()->forget('modal_data') }}
    </script>
@endif
