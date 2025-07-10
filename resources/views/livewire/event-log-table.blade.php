<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search..." wire:model.live.debounce.300ms="search">
        </div>
    </div>
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
                                            <a href="#">{{ $event->causer->id }}</a>
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
                                            {{ class_basename($event->subject) }} #{{ $event->subject->id }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @break

                                @case('properties')
                                    <td>
                                        @if ($event->properties->isNotEmpty())
                                            <pre
                                                class="mb-0"><code>{{ json_encode($event->properties, JSON_PRETTY_PRINT) }}</code></pre>
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