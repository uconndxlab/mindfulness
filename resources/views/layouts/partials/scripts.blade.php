<!-- scripts -->
<script src="{{ asset('js/main.js') }}"></script>
@if(session('modal_data'))
    <script>
        window.sessionModalData = @json(session('modal_data'));
        {{ session()->forget('modal_data') }}
    </script>
@endif
<script src="{{ asset('js/modal.js') }}"></script>
