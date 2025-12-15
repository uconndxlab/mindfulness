<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search email or invited by..." wire:model.live="search">
        </div>
        <div class="col-md-3">
            <select class="form-select" wire:model.live="statusFilter">
                <option value="all">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
                <option value="expired">Expired</option>
                <option value="revoked">Revoked</option>
            </select>
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
        <table class="table table-striped table-nowrap table-bordered">
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
                @forelse ($invitations as $invitation)
                    <tr>
                        @foreach ($columns as $column => $details)
                            @switch($column)
                                @case('email')
                                    <td class="email-column">
                                        <span title="{{ $invitation->email }}">{{ $invitation->email }}</span>
                                    </td>
                                    @break
                                @case('invited_by')
                                    <td>
                                        @if($invitation->invitedBy)
                                            {{ $invitation->invitedBy->name }}<br>
                                            <small class="text-muted">{{ $invitation->invitedBy->email }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    @break
                                @case('status')
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'accepted' => 'success',
                                                'expired' => 'secondary',
                                                'revoked' => 'danger'
                                            ];
                                            $badgeColor = $statusColors[$invitation->status] ?? 'secondary';
                                            $text = $badgeColor === 'secondary' ? 'text-black' : '';
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }} {{ $text }}">
                                            {{ ucfirst($invitation->status) }}
                                        </span>
                                    </td>
                                    @break
                                @case('created_at')
                                    <td>
                                        {{ $invitation->created_at->timezone(auth()->user()->timezone ?? config('app.timezone'))->format('M d, Y g:i A') }}
                                        <br><small class="text-muted">{{ $invitation->created_at->diffForHumans() }}</small>
                                    </td>
                                    @break
                                @case('expires_at')
                                    <td>
                                        {{ $invitation->expires_at->timezone(auth()->user()->timezone ?? config('app.timezone'))->format('M d, Y g:i A') }}
                                        @if($invitation->status === 'pending' && $invitation->expires_at->isPast())
                                            <br><small class="text-danger">(Expired)</small>
                                        @elseif($invitation->status === 'pending' && $invitation->expires_at->diffInHours() < 24)
                                            <br><small class="text-warning">(Expires soon)</small>
                                        @else
                                            <br><small class="text-muted">{{ $invitation->expires_at->diffForHumans() }}</small>
                                        @endif
                                    </td>
                                    @break
                                @case('used_at')
                                    <td>
                                        @if($invitation->used_at)
                                            {{ $invitation->used_at->timezone(auth()->user()->timezone ?? config('app.timezone'))->format('M d, Y g:i A') }}
                                            @if($invitation->registeredUser)
                                                <br><small class="text-muted">by <a href="{{ route('admin.users', ['search' => $invitation->registeredUser->hh_id]) }}">{{ $invitation->registeredUser->hh_id }}</a></small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    @break
                                @case('actions')
                                    <td>
                                        @if($invitation->status === 'pending' && $invitation->expires_at->isFuture())
                                            <div class="btn-group" role="group" aria-label="Invitation Actions">
                                                <button wire:click="resendInvitation({{ $invitation->id }})" class="btn btn-sm btn-info btn-fit" data-bs-toggle="tooltip" title="Resend Invitation">
                                                    <i class="bi bi-envelope-fill"></i>
                                                </button>
                                                <button wire:click="revokeInvitation({{ $invitation->id }})" class="btn btn-sm btn-danger btn-fit" data-bs-toggle="tooltip" title="Revoke Invitation">
                                                    <i class="bi bi-x-circle-fill"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    @break
                            @endswitch
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="text-center text-muted">
                            No invitations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $invitations->links() }}
    </div>
</div>
