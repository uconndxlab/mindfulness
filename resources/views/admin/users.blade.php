@extends('layouts.main')

@section('title', $title)

@section('content')
@php
    use Carbon\Carbon;
@endphp
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
                        <th scope="col" class="text-end">Reminder Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                        @php
                            $locked = $user->lock_access;
                            $is_admin_disable = $user->role === "admin" ? 'disabled' : '';
                        @endphp
                        <tr class="user-row" data-index="{{ $index }}" data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}">
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
                            <td id="email_div_{{ $index }}" class="text-end">
                                @php
                                    $remind_limit = (int) getConfig('remind_email_day_limit', 0);
                                    $last_active = $user->last_active_at ? Carbon::parse($user->last_active_at) : null;
                                    $last_reminded = $user->last_reminded_at ? Carbon::parse($user->last_reminded_at) : null;
                                    $remind_disabled = $user->lock_access || ($last_active && ($last_active->diffInDays(now()) < $remind_limit)) || ($last_reminded && ($last_reminded->diffInDays(now()) < $remind_limit)) ? 'disabled' : '';
                                    $next_ping_time = null;
                                    $last_type = null;
                                    $last_time = null;
                                    if (!$user->lock_access && ($last_active || $last_reminded)) {
                                        if ($last_active) {
                                            if ($last_reminded && $last_reminded->gt($last_active)) {
                                                $last_type = 'reminded';
                                                $next_ping_time = $last_reminded->copy()->addDays($remind_limit);
                                                $last_time = $last_reminded->format('Y-m-d H:i:s');
                                            } else {
                                                $last_type = 'active';
                                                $next_ping_time = $last_active->copy()->addDays($remind_limit);
                                                $last_time = $last_active->format('Y-m-d H:i:s');
                                            }
                                        } else {
                                            $last_type = 'reminded';
                                            $next_ping_time = $last_reminded->copy()->addDays($remind_limit);
                                            $last_time = $last_reminded->format('Y-m-d H:i:s');
                                        }
                                    }
                                @endphp
                                <button id="email_button_{{ $index }}" class="btn btn-primary {{ $remind_disabled }}" {{ $remind_disabled }}>
                                    @if($remind_disabled)
                                        @if($user->lock_access)
                                            Account is locked
                                        @else
                                            Last {{ $last_type }} at {{ $last_time }}. Can ping user at {{ $next_ping_time->format('Y-m-d H:i:s') }}.
                                        @endif
                                    @else
                                        Remind {{ $user->name }} to come back to app
                                    @endif
                                </button>
                            </td>
                            <td id="del_acc_div_{{ $index }}" class="text-end">
                                <button id="del_button_{{ $index }}" class="btn btn-danger {{ $is_admin_disable }}" {{ $is_admin_disable }}>
                                    <i id="del_icon_{{ $index }}" class="bi bi-x"></i> Delete Account
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
            const name = userRow.getAttribute('data-name');
            const email = userRow.getAttribute('data-email');
            const lockButton = document.getElementById('lock_button_'+index);
            lockButton.addEventListener('click', function() {
                changeAccess(index, userId);
            })
            const emailButton = document.getElementById('email_button_'+index);
            emailButton.addEventListener('click', function() {
                emailPingUser(index, userId);
            })
            const delButton = document.getElementById('del_button_'+index);
            delButton.addEventListener('click', function() {
                showModal({
                    label: `Delete Account: (${name}, ${email})`,
                    body: 'Are you sure you want to delete this account?',
                    media: null,
                    route: '{{ route('users.delete', ['user_id' => $user->id]) }}',
                    method: 'DELETE',
                    buttonLabel: 'DELETE',
                    buttonClass: 'btn-danger'
                });
            })
        });

        function changeAccess(index, userId) {
            console.log('ACCESS');
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
                    window.location.reload();
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

        function emailPingUser(index, userId) {
            console.log('EMAIL');
            errDiv.style.display = 'none';
            console.log('clicked index: ', index);
            console.log('userId: ', userId);
    
            return new Promise((resolve, reject) => {
                axios.post('/emailRemindUser/'+userId, {
                    user_id: userId
                }, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    console.log(response.data.success);
                    const btn_clicked = document.getElementById('email_button_'+index);
                    btn_clicked.disabled = true;
                    btn_clicked.textContent = 'Email sent!';
                    btn_clicked.classList.add('disabled');
                    resolve(true);
                })
                .catch(error => {
                    console.error('Error pinging user: ', error);
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
