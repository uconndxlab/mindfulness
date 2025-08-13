@extends('layouts.admin')

@section('admin_content')
    <h1>Dashboard</h1>
    <div class="row">
        <button id="lock_button_reg" class="btn btn-{{ $registration_locked ? 'primary' : 'danger'}}">
            <i id="lock_icon_reg" class="bi bi-{{ $registration_locked ? 'unlock' : 'lock'}}"></i>
            <span id="lock_text_reg">{{ $registration_locked ? 'UNLOCK REGISTRATION' : 'Lock Registration'}}</span>
        </button>
    </div>
    <script >
        document.getElementById('lock_button_reg').addEventListener('click', function() {
            const button = this;
            const icon = document.getElementById('lock_icon_reg');
            const text = document.getElementById('lock_text_reg');

            fetch('{{ route('admin.lock-registration-access') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Server error');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log(data.success);

                // update button
                const isLocked = data.status;
                button.classList.toggle('btn-danger', !isLocked);
                button.classList.toggle('btn-primary', isLocked);
                icon.classList.toggle('bi-lock', !isLocked);
                icon.classList.toggle('bi-unlock', isLocked);
                text.textContent = isLocked ? 'UNLOCK REGISTRATION' : 'Lock Registration';
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
@endsection
