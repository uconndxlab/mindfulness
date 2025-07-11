<?php

namespace App\Livewire;

use App\Models\EventLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Component;
use Livewire\WithPagination;

class EventLogTable extends Component
{
    use WithPagination;

    public $sortColumn = 'created_at';
    public $sortDirection = 'desc';
    public $search = '';

    public $columns = [
        'created_at' => ['label' => 'Timestamp', 'sortable' => true],
        'causer' => ['label' => 'User', 'sortable' => false],
        'description' => ['label' => 'Action', 'sortable' => true],
        'subject' => ['label' => 'Subject', 'sortable' => false],
        'properties' => ['label' => 'Details', 'sortable' => false],
    ];

    public function sortBy($column)
    {
        if ($this->columns[$column]['sortable']) {
            if ($this->sortColumn === $column) {
                $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                $this->sortColumn = $column;
                $this->sortDirection = 'asc';
            }
        }
        $this->resetPage();
    }

    public function render()
    {
        $query = EventLog::with([
            'causer:id,hh_id,name',
            'subject',
        ])->orderBy($this->sortColumn, $this->sortDirection)->orderBy('id', $this->sortDirection);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                    ->orWhere('event', 'like', '%' . $this->search . '%')
                    ->orWhereHas('subject', function ($q) {
                        $q->where('title', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('causer', function ($q) {
                        $q->where('hh_id', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $events = $query->paginate(10);

        return view('livewire.event-log-table', [
            'events' => $events,
        ]);
    }
}
