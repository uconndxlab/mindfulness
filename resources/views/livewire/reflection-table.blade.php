<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search..." wire:model.live.debounce.300ms="search"/>
        </div>
        <div>
            <a href="{{ route('admin.reflections.export') }}" class="btn btn-info btn-sm m-0">Export to CSV</a>
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
                @forelse ($reflections as $reflection)
                    <tr>
                        @foreach ($columns as $column => $details)
                            @switch($column)
                                @case('hh_id')
                                    <td>
                                        @if($reflection->user->hh_id)
                                            <a href="{{ route('admin.users', ['search' => $reflection->user->hh_id]) }}">{{ $reflection->user->hh_id }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @break

                                @case('quiz')
                                    <td>
                                        @if($reflection->activity?->title)
                                            {{ $reflection->activity->title }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @break

                                @case('subject')
                                    <td>
                                        @if($reflection->subject)
                                            @if($reflection->subject_type === 'App\Models\Activity')
                                                <strong>Activity:</strong> {{ $reflection->subject->title }}
                                            @elseif($reflection->subject_type === 'App\Models\Module')
                                                <strong>Module:</strong> {{ $reflection->subject->name }}
                                            @else
                                                -
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @break

                                @case('reflection_type')
                                    <td>
                                        @if($reflection->reflection_type === 'check_in')
                                            <span class="badge bg-primary">Quick Check-In</span>
                                        @elseif($reflection->reflection_type === 'self_rating')
                                            <span class="badge bg-success">Self-Rating</span>
                                        @else
                                            <span class="badge bg-secondary text-black">Other</span>
                                        @endif
                                    </td>
                                    @break
                                
                                @case('answers')
                                    <td>{{ $this->formatAnswers($reflection->answers) }}</td>
                                    @break

                                @case('detailed_answers')
                                    <td>
                                        <button class="btn btn-info btn-sm" data-open-modal data-modal-label="Answer Details" data-modal-body-from="#details-{{ $reflection->id }}">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <div id="details-{{ $reflection->id }}" class="d-none">
                                            <x-detailed-answers :reflection="$reflection" />
                                        </div>
                                    </td>
                                    @break
                                
                                @case('created_at')
                                    <td>{{ $reflection->created_at?->format('Y-m-d H:i:s') }}</td>
                                    @break
                                
                                @case('updated_at')
                                    <td>{{ $reflection->updated_at?->format('Y-m-d H:i:s') }}</td>
                                    @break

                            @endswitch
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No reflections found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $reflections->links() }}
        </div>
    </div>
</div>
