<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search..." wire:model.live="search">
        </div>
        <div>
            <a href="{{ route('admin.notes.export') }}" class="btn btn-info btn-sm m-0">Export to CSV</a>
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
                @forelse ($notes as $note)
                    <tr>
                        @foreach ($columns as $column => $details)
                            @switch($column)
                                @case('hh_id')
                                    <td>
                                        @if($note->user->hh_id)
                                            <a href="{{ route('admin.users', ['search' => $note->user->hh_id]) }}">{{ $note->user->hh_id }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                @break

                                @case('topic')
                                    <td class="no-links">
                                        @if($note->topic)
                                            @markdown(ucfirst(strip_tags($note->topic)))
                                        @else
                                            -
                                        @endif
                                    </td>
                                @break

                                @case('note')
                                    <td class="no-links note-cell">
                                        <div class="note-cell-content">
                                            @if($note->note)
                                                {{ $note->note }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>
                                @break

                                @case('created_at')
                                    <td>{{ $note->created_at?->format('Y-m-d H:i:s') }}</td>
                                @break

                                @case('updated_at')
                                    <td>{{ $note->updated_at?->format('Y-m-d H:i:s') }}</td>
                                @break
                            @endswitch
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No notes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $notes->links() }}
    </div>
</div>

