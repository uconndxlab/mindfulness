<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search..." wire:model.live="search">
        </div>
    </div>
    <div class="table-responsive">
        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <table class="table table-striped table-nowrap">
            <thead>
                <tr>
                    @foreach ($columns as $column => $details)
                        @if ($details['sortable'])
                            <th class="sortable-header" scope="col">
                                <a href="#" wire:click.prevent="sortBy('{{ $column }}')" class="text-dark text-decoration-none">
                                    {{ $details['label'] }}
                                @if($sortColumn === $column)
                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up"></i>
                                @endif
                                </a>
                            </th>
                        @else
                            <th scope="col">{{ $details['label'] }}</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        @foreach ($columns as $column => $details)
                            @switch($column)
                                @case('hh_id')
                                    <th scope="row">{{ $user->hh_id }}</th>
                                    @break
                                @case('name')
                                    <td>{{ $user->name }}</td>
                                    @break
                                @case('email')
                                    <td>
                                        <span title="{{ $user->email }}">{{ $user->email }}</span>
                                    </td>
                                    @break
                                @case('role')
                                    <td class="text-center">
                                        <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : 'info' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    @break
                                @case('created_at')
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    @break
                                @case('current_activity')
                                    @if ($user->currentActivity())
                                        <td>
                                            {{ $user->currentActivity()->title }}<br>
                                            <small>{{ $user->currentActivity()->day->name }}, {{ $user->currentActivity()->day->module->name }}</small>
                                        </td>
                                    @else
                                        <td>None</td>
                                    @endif
                                    @break
                                @case('last_active_at')
                                    <td>{{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never' }}</td>
                                    @break
                                @case('verified')
                                    <td class="text-center">
                                        <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'danger' }}">
                                            {{ $user->email_verified_at ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    @break
                                @case('last_reminded_at')
                                    <td>{{ $user->last_reminded_at ? $user->last_reminded_at->diffForHumans() : 'Never' }}</td>
                                    @break
                                @case('access')
                                    <td class="text-center">
                                        <span class="badge bg-{{ $user->lock_access ? 'danger' : 'success' }}">
                                            {{ $user->lock_access ? 'Locked' : 'Active' }}
                                        </span>
                                    </td>
                                    @break
                                @case('actions')
                                    <td>
                                        <div class="btn-group" role="group" aria-label="User Actions">
                                            <button wire:click="toggleAccess({{ $user->id }})" class="btn btn-sm btn-{{ $user->lock_access ? 'success' : 'danger' }} btn-fit" data-bs-toggle="tooltip" title="{{ $user->lock_access ? 'Unlock Access' : 'Lock Access' }}">
                                                <i class="bi bi-{{ $user->lock_access ? 'unlock-fill' : 'lock-fill' }}"></i>
                                            </button>
                                            <button wire:click="sendReminder({{ $user->id }})" class="btn btn-sm btn-info {{ !$user->canSendReminder() ? 'disabled' : '' }} btn-fit" data-bs-toggle="tooltip" title="Send Reminder Email">
                                                <i class="bi bi-envelope-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                    @break
                            @endswitch
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $users->links() }}
    </div>
</div>