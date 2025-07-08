<div class="table-responsive">
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
                            @case('id')
                                <th scope="row">{{ $user->id }}</th>
                                @break
                            @case('name')
                                <td>{{ $user->name }}</td>
                                @break
                            @case('email')
                                <td>
                                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
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
                                <td>TODO</td>
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
                            @case('last_reminder_at')
                                <td>{{ $user->last_reminder_at ? $user->last_reminder_at->diffForHumans() : 'Never' }}</td>
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
                                    TODO
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
