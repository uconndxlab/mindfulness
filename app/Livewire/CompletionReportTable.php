<?php

namespace App\Livewire;

use App\Enums\MilestoneType;
use App\Models\CompletionReport;
use App\Services\CompletionReportService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CompletionReportTable extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $hh_id = '';

    public $sortColumn = 'completed_at';
    public $sortDirection = 'desc';

    public $sendConfirmHhId = null;
    public $sendConfirmEmail = null;
    public $sendConfirmLastSent = null;

    public $columns = [
        'hh_id' => ['label' => 'HH ID', 'sortable' => true],
        'registered_at' => ['label' => 'Time Registered', 'sortable' => true],
        'completed_at' => ['label' => 'Time Completed', 'sortable' => true],
        'last_sent_at' => ['label' => 'Last Sent At', 'sortable' => true],
        'actions' => ['label' => 'Actions', 'sortable' => false],
    ];

    public function sortBy($column)
    {
        if ($this->columns[$column]['sortable'] ?? false) {
            if ($this->sortColumn === $column) {
                $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                $this->sortColumn = $column;
                $this->sortDirection = 'asc';
            }
        }

        $this->resetPage();
    }

    public function promptSendReport(string $hhId)
    {
        $report = CompletionReport::query()
            ->with('user')
            ->whereHas('user', fn ($query) => $query->where('hh_id', $hhId))
            ->firstOrFail();

        $this->sendConfirmHhId = $hhId;
        $this->sendConfirmEmail = $report->user->email;
        $this->sendConfirmLastSent = $report->last_sent_at
            ? $report->last_sent_at->timezone(auth()->user()->timezone ?? config('app.timezone'))->format('M d, Y g:i A')
            : null;
    }

    public function cancelSendReport()
    {
        $this->reset(['sendConfirmHhId', 'sendConfirmEmail', 'sendConfirmLastSent']);
    }

    public function confirmSendReport(CompletionReportService $completionReportService)
    {
        if (!$this->sendConfirmHhId) {
            return;
        }

        try {
            $report = CompletionReport::query()
                ->with('user')
                ->whereHas('user', fn ($query) => $query->where('hh_id', $this->sendConfirmHhId))
                ->firstOrFail();

            $completionReportService->sendToUser($report);

            session()->flash('message', 'Journey report sent to ' . $report->user->email . '.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to send report. Please try again.');
        }

        $this->cancelSendReport();
    }

    public function render()
    {
        $query = CompletionReport::query()
            ->with(['user'])
            ->join('users', 'users.id', '=', 'completion_reports.user_id')
            ->leftJoin('user_milestones', function ($join) {
                $join->on('user_milestones.user_id', '=', 'users.id')
                    ->where('user_milestones.type', MilestoneType::Module4->value);
            })
            ->select(
                'completion_reports.*',
                'users.hh_id',
                'users.created_at as registered_at',
                'user_milestones.achieved_at as completed_at',
            );

        if (!empty($this->search)) {
            $query->where('users.hh_id', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->hh_id)) {
            $query->where('users.hh_id', $this->hh_id);
        }

        $sortColumn = match ($this->sortColumn) {
            'hh_id' => 'users.hh_id',
            'registered_at' => 'users.created_at',
            'completed_at' => 'user_milestones.achieved_at',
            'last_sent_at' => 'completion_reports.last_sent_at',
            default => 'user_milestones.achieved_at',
        };

        $reports = $query
            ->orderBy($sortColumn, $this->sortDirection)
            ->orderBy('completion_reports.id', $this->sortDirection)
            ->paginate(10);

        return view('livewire.completion-report-table', [
            'reports' => $reports,
        ]);
    }
}
