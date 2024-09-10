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
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th scope="col">Email</th>
                        <th scope="col">Name</th>
                        <th scope="col">Last Active</th>
                        <th scope="col">Role</th>
                        <th scope="col" class="text-end">Change Access</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                        @php
                            $locked = $user->lock_access;
                            $is_admin_disable = $user->role === "admin" ? 'disabled' : '';
                        @endphp
                        <tr class="user-row" data-index="{{ $index }}" data-id="{{ $user->id }}">
                            <td style="word-wrap: break-word;">
                                {{ $user->email }}
                            </td>
                            <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $user->name }}
                            </td>
                            <td>
                                {{ $user->formatted_time }}
                            </td>
                            <td>
                                {{ ucfirst($user->role) }}
                            </td>
                            <td id="lock_div_{{ $index }}" class="text-end">
                                <button id="lock_button_{{ $index }}" class="btn btn-{{ $locked ? 'danger' : 'primary'}} {{ $is_admin_disable }}" {{ $is_admin_disable }}>
                                    <i id="lock_icon_{{ $index }}" class="bi bi-{{ $locked ? 'unlock' : 'lock'}}"></i> {{ $locked ? 'UNLOCK ACCOUNT' : 'Lock account'}}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
