<!-- scripts -->
<script src="{{ asset('js/main.js') }}"></script>
@if($is_app)
    @if(session('modal_data'))
        <script>
            window.sessionModalData = @json(session('modal_data'));
            {{ session()->forget('modal_data') }}
        </script>
    @endif
    <script src="{{ asset('js/modal.js') }}"></script>
@endif

<!-- ga event -->
<script>
    // function to fire GA event
    function fireGAEvent(eventData) {
        console.log('fireGAEvent');
        if (eventData && eventData.name && eventData.params) {
            console.log('ga_event from response', eventData);
            gtag('event', eventData.name, eventData.params);
        }
    }
</script>
@if(session('ga_event'))
    <script>
        // fire GA event from session
        fireGAEvent(@json(session('ga_event')));
        {{ session()->forget('ga_event') }}
    </script>
@endif
