<!-- scripts -->
@livewireScripts

@php
    $page_type = isset($layout_type) ? $layout_type : 'auth';
@endphp

@if(session('modal_data'))
    <meta name="session-modal-data" content='@json(session('modal_data'))'>
    {{ session()->forget('modal_data') }}
@endif
