<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search..." wire:model.live.debounce.300ms="search"/>
        </div>
        <div>
            <a href="{{ route('admin.events.export') }}" class="btn btn-info btn-sm" style="margin: 0 !important;">Export to CSV</a>
        </div>
    </div>
    <div class="table-responsive">
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
                @forelse ($events as $event)
                    <tr>
                        @foreach ($columns as $column => $details)
                            @switch($column)
                                @case('created_at')
                                    <td>{{ $event->created_at->format('M d, Y h:i A') }}</td>
                                @break

                                @case('causer')
                                    <td>
                                        @if ($event->causer)
                                            <a href="{{ route('admin.users', ['search' => $event->causer->hh_id]) }}">{{ $event->causer->hh_id }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @break

                                @case('description')
                                    <td>{{ $event->description }}</td>
                                @break

                                @case('subject')
                                    <td>
                                        @if ($event->subject)
                                            @switch(class_basename($event->subject))
                                                @case('User')
                                                    <a href="{{ route('admin.users', ['search' => $event->subject->hh_id]) }}">
                                                        {{ $event->subject->hh_id }}
                                                    </a>
                                                    @break
                                                @case('Activity')
                                                    <strong>{{ $event->subject->title }}</strong><br>
                                                    <small>{{ $event->subject->day->name }}, {{ $event->subject->day->module->name }}</small>
                                                    @break
                                                @case('Day')
                                                    <strong>{{ $event->subject->name }}</strong><br>
                                                    <small>{{ $event->subject->module->name }}</small>
                                                    @break
                                                @case('Module')
                                                    <strong>{{ $event->subject->name }}</strong>
                                                    @break
                                                @default
                                                    {{ class_basename($event->subject) }} #{{ $event->subject->id }}
                                            @endswitch
                                        @else
                                            -
                                        @endif
                                    </td>
                                @break

                                @case('properties')
                                    <td>
                                        @if ($event->properties->isNotEmpty())
                                            <button class="btn btn-info btn-sm"
                                                onclick="showModal({
                                                    label: 'Event Details',
                                                    body: document.getElementById('details-{{ $event->id }}').innerHTML,
                                                    closeLabel: 'Close'
                                                })">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                            <div id="details-{{ $event->id }}" class="d-none">
                                                <x-json-properties-table :data="$event->properties" />
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @break
                            @endswitch
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="text-center">No events found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $events->links() }}
    </div>
</div>