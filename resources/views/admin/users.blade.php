@extends('layouts.main')

@section('title', $title)

@section('content')
<div class="col-md-10">
    <h2><strong>{{ $head }}</strong></h2>
    <div id="error-messages" class="alert alert-danger" style="display: none;"></div>
    <div id="lock_div_reg">
        <button id="lock_button_reg" class="btn btn-{{ $registration_locked ? 'danger' : 'primary'}}">
            <i id="lock_icon_reg" class="bi bi-{{ $registration_locked ? 'unlock' : 'lock'}}"></i> {{ $registration_locked ? 'UNLOCK REGISTRATION' : 'Lock registration'}}
        </button>
    </div>

    <div class="container mt-4">
        <div class="sticky-top" style="background-color:white">
            <div class="row align-items-center mb-2">
                <div class="col-md-2 fw-bold">
                    Email
                </div>
                <div class="col-md-2 fw-bold">
                    Name
                </div>
                <div class="col-md-5 fw-bold">
                    Last Active
                </div>
                <div class="col-md-3 fw-bold text-end">
                    Change Access
                </div>
            </div>
            <div class="col-12">
                <hr class="separator-line">
            </div>
        </div>
        @foreach($users as $index => $user)
            @php
                $locked = $user->lock_access;
            @endphp
            <div class="row align-items-center mb-2 user-row" data-index="{{ $index }}" data-id="{{ $user->id }}">
                <div class="col-md-2">
                    {{ $user->email }}
                </div>
                <div class="col-md-2">
                    {{ $user->name }}
                </div>
                <div class="col-md-5">
                    {{ $user->formatted_time }}
                </div>
                <div id="lock_div_{{ $index }}" class="col-md-3 text-end">
                    <button id="lock_button_{{ $index }}" class="btn btn-{{ $locked ? 'danger' : 'primary'}}">
                        <i id="lock_icon_{{ $index }}" class="bi bi-{{ $locked ? 'unlock' : 'lock'}}"></i> {{ $locked ? 'UNLOCK ACCOUNT' : 'Lock account'}}
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <hr class="separator-line">
                </div>
            </div>
        @endforeach
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const errDiv = document.getElementById('error-messages');

        //handle un/locking registration
        document.getElementById('lock_button_reg').addEventListener('click', function() {
            registrationAccess();
        });

        function registrationAccess() {
            errDiv.style.display = 'none';
            return new Promise((resolve, reject) => {
                axios.post('/registrationLock', {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    console.log(response.data.success);
                    const locked = response.data.status;
                    const lockDiv = document.getElementById('lock_div_reg');
                    if (locked) {
                        lockDiv.innerHTML = `
                            <button id="lock_button_reg" class="btn btn-danger">
                                <i id="lock_icon_reg" class="bi bi-unlock"></i> UNLOCK REGISTRATION
                            </button>
                        `;
                    } else {
                        lockDiv.innerHTML = `
                            <button id="lock_button_reg" class="btn btn-primary">
                                <i id="lock_icon_reg" class="bi bi-lock"></i> Lock registration
                            </button>
                        `;
                    }
                    const lockButton = document.getElementById('lock_button_reg');
                    lockButton.addEventListener('click', function() {
                        registrationAccess();
                    })
                    resolve(true);
                })
                .catch(error => {
                    console.error('Error changing access: ', error);
                    //other errors
                    const errorMessages = error.response?.data?.error_message || 'An unknown error occurred.';
                    errDiv.textContent = errorMessages;
                    errDiv.style.display = 'block';
                    reject(false);
                });
            });
        }


        document.querySelectorAll('.user-row').forEach(userRow => {
            const index = userRow.getAttribute('data-index');
            const userId = userRow.getAttribute('data-id');
            const lockButton = document.getElementById('lock_button_'+index);
            lockButton.addEventListener('click', function() {
                changeAccess(index, userId);
            })
        });

        function changeAccess(index, userId) {
            errDiv.style.display = 'none';
            console.log('clicked index: ', index);
            console.log('userId: ', userId);
    
            return new Promise((resolve, reject) => {
                axios.post('/changeAccess/'+userId, {
                    user_id: userId
                }, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    console.log(response.data.success);
                    const locked = response.data.status;
                    const lockDiv = document.getElementById('lock_div_'+index);
                    if (locked) {
                        lockDiv.innerHTML = `
                            <button id="lock_button_${index}" class="btn btn-danger">
                                <i id="lock_icon_${index}" class="bi bi-unlock"></i> UNLOCK ACCOUNT
                            </button>
                        `;
                    } else {
                        lockDiv.innerHTML = `
                            <button id="lock_button_${index}" class="btn btn-primary">
                                <i id="lock_icon_${index}" class="bi bi-lock"></i> Lock account
                            </button>
                        `;
                    }
                    const lockButton = document.getElementById('lock_button_'+index);
                    lockButton.addEventListener('click', function() {
                        changeAccess(index, userId);
                    })
                    resolve(true);
                })
                .catch(error => {
                    console.error('Error changing access: ', error);
                    //other errors
                    const errorMessages = error.response?.data?.error_message || 'An unknown error occurred.';
                    errDiv.textContent = errorMessages;
                    errDiv.style.display = 'block';
                    reject(false);
                });
            });
        }
    });

</script>
@endsection
