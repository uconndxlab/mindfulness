<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Last Active</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3 text-end">Change Access</th>
                            <th class="px-4 py-3 text-end">Reminder Email</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                            <th class="px-4 py-3 text-end">Current Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                            @php
                                $locked = $user->lock_access;
                                $is_admin_disable = $user->isAdmin() ? 'disabled' : '';
                            @endphp
                            <tr class="user-row" data-index="{{ $index }}" data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}">
                                <td class="px-4 py-3 text-break">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-truncate" style="max-width: 200px;">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->formatted_time }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-{{ $user->isAdmin() ? 'danger' : 'success' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td id="lock_div_{{ $index }}" class="px-4 py-3 text-end">
                                    <button id="lock_button_{{ $index }}" class="btn btn-{{ $locked ? 'danger' : 'success' }} btn-sm {{ $is_admin_disable }}" {{ $is_admin_disable }}>
                                        <i class="bi bi-{{ $locked ? 'unlock' : 'lock'}} me-1"></i>
                                        {{ $locked ? 'Unlock' : 'Lock'}}
                                    </button>
                                </td>
                                <td id="email_div_{{ $index }}" class="px-4 py-3 text-end">
                                    @php
                                        $remind_limit = (int) config('mail.remind_email_day_limit');
                                        $last_active = $user->last_active_at ? Carbon::parse($user->last_active_at) : null;
                                        $last_reminded = $user->last_reminded_at ? Carbon::parse($user->last_reminded_at) : null;
                                        $now = Carbon::now();
        
                                        $next_remind_date = null;
                                        if ($last_active) {
                                            $next_remind_date = $last_active->copy()->addDays($remind_limit);
                                        }
                                        if ($last_reminded && (!$next_remind_date || $last_reminded->gt($last_active))) {
                                            $next_remind_date = $last_reminded->copy()->addDays($remind_limit);
                                        }
                                        
                                        $remind_disabled = $user->lock_access || ($next_remind_date && $next_remind_date->gt($now));
                                    @endphp

                                    <button id="email_button_{{ $index }}" class="btn btn-info btn-sm {{ $remind_disabled ? 'disabled' : '' }}">
                                        @if($user->lock_access)
                                            Account Locked
                                        @elseif ($remind_disabled && $next_remind_date)
                                            Remind again {{ $next_remind_date->diffForHumans(['parts' => 2]) }}
                                        @else
                                            <i class="bi bi-envelope me-1"></i>
                                            Send Reminder
                                        @endif
                                    </button>
                                </td>
                                <td id="del_acc_div_{{ $index }}" class="px-4 py-3 text-end">
                                    <button id="del_button_{{ $index }}" class="btn btn-danger btn-sm {{ $is_admin_disable }}" {{ $is_admin_disable }}>
                                        <i class="bi bi-trash me-1"></i>
                                        Delete
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $current_activity = $user->currentActivity();
                                    @endphp
                                    @if($current_activity)
                                        <span class="fw-bold text-primary">Part {{ $current_activity->day->module->id }},</span> 
                                        <span class="fw-semibold">{{ $current_activity->day->name }}</span> - 
                                        {{ $current_activity->title }}
                                    @else
                                        <span class="text-muted">No activity</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const errDiv = document.getElementById('error-messages');


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
                    route: `/deleteUser/${userId}`,
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
