<?php

namespace App\Livewire;

use App\Models\Note;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class NoteTable extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';
    public $sortColumn = 'created_at';
    public $sortDirection = 'desc';

    public $columns = [
        'hh_id' => ['label' => 'User ID', 'sortable' => false],
        'topic' => ['label' => 'Topic', 'sortable' => true],
        'note' => ['label' => 'Journal', 'sortable' => false],
        'created_at' => ['label' => 'Created At', 'sortable' => true],
        'updated_at' => ['label' => 'Updated At', 'sortable' => true],
    ];

    public function sortBy($column)
    {
        \Log::info('Sorting by column: ' . $column);
        if (isset($this->columns[$column]['sortable']) && $this->columns[$column]['sortable']) {
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
        $query = Note::with(['user:id,hh_id', 'activity:id,title'])
            ->orderBy($this->sortColumn, $this->sortDirection);

        // search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('topic', 'like', '%' . $this->search . '%')
                    ->orWhereHas('activity', function ($q) {
                        $q->where('title', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('user', function ($q) {
                        $q->where('hh_id', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $notes = $query->paginate(10);

        return view('livewire.note-table', [
            'notes' => $notes
        ]);
    }
}

