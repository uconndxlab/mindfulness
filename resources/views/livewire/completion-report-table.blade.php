<div id="completion-reports-table" class="container-fluid" @if ($hh_id) data-highlight-hh-id="{{ $hh_id }}" @endif>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search by HH ID..." wire:model.live.debounce.300ms="search"/>
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
                @forelse ($reports as $report)
                    <tr @class(['table-warning' => $hh_id && $report->hh_id === $hh_id]) id="report-row-{{ $report->hh_id }}">
                        @foreach ($columns as $column => $details)
                            @switch($column)
                                @case('hh_id')
                                    <th scope="row">
                                        <a href="{{ route('admin.users', ['search' => $report->hh_id]) }}">{{ $report->hh_id }}</a>
                                    </th>
                                    @break
                                @case('registered_at')
                                    <td>
                                        @if ($report->registered_at)
                                            {{ \Carbon\Carbon::parse($report->registered_at)->timezone(auth()->user()->timezone ?? config('app.timezone'))->format('M d, Y g:i A') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @break
                                @case('completed_at')
                                    <td>
                                        @if ($report->completed_at)
                                            {{ \Carbon\Carbon::parse($report->completed_at)->timezone(auth()->user()->timezone ?? config('app.timezone'))->format('M d, Y g:i A') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @break
                                @case('last_sent_at')
                                    <td>
                                        @if ($report->last_sent_at)
                                            {{ $report->last_sent_at->timezone(auth()->user()->timezone ?? config('app.timezone'))->format('M d, Y g:i A') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    @break
                                @case('actions')
                                    <td>
                                        @if ($report->pdf_path)
                                            <div class="btn-group" role="group" aria-label="Completion Report Actions">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary btn-fit js-view-completion-pdf"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#pdfModal"
                                                    data-pdf-url="{{ route('admin.completion-reports.pdf', $report->hh_id) }}"
                                                    data-pdf-title="Completion Report — {{ $report->hh_id }}"
                                                    title="View Report"
                                                >
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </button>
                                                <a
                                                    href="{{ route('admin.completion-reports.download', $report->hh_id) }}"
                                                    class="btn btn-sm btn-info btn-fit"
                                                    data-bs-toggle="tooltip"
                                                    title="Download Report"
                                                >
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <button
                                                    type="button"
                                                    wire:click="promptSendReport('{{ $report->hh_id }}')"
                                                    class="btn btn-sm btn-success btn-fit"
                                                    data-bs-toggle="tooltip"
                                                    title="Send Report to User"
                                                >
                                                    <i class="bi bi-envelope-fill"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-muted">No PDF</span>
                                        @endif
                                    </td>
                                    @break
                            @endswitch
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="text-center text-muted">
                            No completion reports found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $reports->links() }}
    </div>

    @if ($sendConfirmHhId)
        <div class="modal fade show d-block livewire-modal-backdrop" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Journey Report</h5>
                        <button type="button" class="btn-close" wire:click="cancelSendReport" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Send the Part 4 journey report email to <strong>{{ $sendConfirmEmail }}</strong> ({{ $sendConfirmHhId }})?</p>
                        <p class="mb-0">
                            @if ($sendConfirmLastSent)
                                <strong>Last sent:</strong> {{ $sendConfirmLastSent }}
                            @else
                                <strong>Last sent:</strong> Never sent
                            @endif
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelSendReport">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="confirmSendReport">Send Report</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
