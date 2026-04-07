<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex gap-2 align-items-center">
            <div class="position-relative">
                <button class="btn btn-outline-secondary d-flex align-items-center gap-1 table-btn m-0" 
                    wire:click="toggleFilters"
                    type="button">
                    <i class="bi bi-sliders"></i>
                    <span>Filter</span>
                    <i class="bi bi-chevron-{{ $showFilters ? 'up' : 'down' }} ms-1"></i>
                </button>
                @if($showFilters)
                    <div class="position-absolute bg-white border rounded shadow-sm p-3 mt-1 table-filter-container" wire:click.outside="toggleFilters">
                        <div class="mb-3">
                            <div class="fw-bold mb-2">Milestones</div>
                            @foreach ($milestoneTypes as $milestoneType)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" 
                                        type="checkbox" 
                                        wire:model="milestones"
                                        id="milestone_{{ $milestoneType->value }}" 
                                        value="{{ $milestoneType->value }}">
                                    <label class="form-check-label" for="milestone_{{ $milestoneType->value }}">
                                        {{ $milestoneType->label() }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <button wire:click="applyFilters" type="button" class="btn btn-primary">Apply Filter</button>
                        <button wire:click="clearFilters" type="button" class="btn btn-link text-center mt-1 mb-2 text-dark">Clear Filters</button>
                    </div>
                @endif
            </div>
            <div>
                <input type="text" class="form-control" placeholder="Search..." wire:model.live="search">
            </div>
        </div>
        <div>
            <a href="{{ route('admin.users.export') }}" class="btn btn-info btn-sm table-btn m-0">Export to CSV</a>
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
                                    <td class="email-column">
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
                                @case('milestones')
                                    <td class="milestones-column">
                                        @foreach ($user->milestones->sortBy('achieved_at') as $milestone)
                                            <span class="badge bg-{{ $milestone->type->color() }} mb-1"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Achieved: {{ $milestone->achieved_at->format('M d, Y g:i A') }} (UTC)">
                                                {{ $milestone->type->badgeLabel() }}
                                            </span>
                                        @endforeach
                                    </td>
                                    @break
                                @case('created_at')
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    @break
                                @case('current_activity')
                                    @if ($user->currentActivity())
                                        <td>
                                            {{ $user->currentActivity()->title }}<br>
                                            <small>{{ $user->currentActivity()->day->name }}, {{ $user->currentActivity()->day->module->partName() }}</small>
                                        </td>
                                    @else
                                        <td>None</td>
                                    @endif
                                    @break
                                @case('last_active_at')
                                    <td>{{ $user->last_active_at ? $user->last_active_at->diffForHumans() : 'Never' }}</td>
                                    @break
                                @case('num_favorites')
                                    <td class="text-center">
                                        @if ($user->favoritedActivities->isNotEmpty())
                                            <button class="btn btn-link text-link" 
                                                data-bs-toggle="tooltip"
                                                title="View Favorites"
                                                data-open-modal
                                                data-modal-label="Favorites"
                                                data-modal-body-from="#favorites-{{ $user->id }}">
                                                {{ $user->favoritedActivities->count() }} <i class="bi bi-star-fill"></i>
                                            </button>
                                            <div id="favorites-{{ $user->id }}" class="d-none">
                                                @php
                                                    $groupedByModule = $user->favoritedActivities->groupBy('day.module.id');
                                                @endphp
                                                <div class="border rounded p-3 bg-light favorite-list-container">
                                                    @foreach ($groupedByModule as $moduleId => $activitiesInModule)
                                                        <strong>{{ $activitiesInModule->first()->day->module->partName() }}</strong>
                                                        @php
                                                            $groupedByDay = $activitiesInModule->groupBy('day.id');
                                                        @endphp
                                                        <ul class="mb-2">
                                                            @foreach ($groupedByDay as $dayId => $activitiesInDay)
                                                                <li>
                                                                    <strong>{{ $activitiesInDay->first()->day->name }}:</strong>
                                                                    <ul class="mb-0">
                                                                        @foreach ($activitiesInDay as $activity)
                                                                            <li>{{ $activity->title }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            0
                                        @endif
                                    </td>
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