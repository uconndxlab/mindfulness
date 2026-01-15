<?php

namespace App\Livewire;

use App\Models\QuizAnswers;
use App\Services\QuizAnswerFormatter;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ReflectionTable extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';
    public $sortColumn = 'created_at';
    public $sortDirection = 'desc';

    public $columns = [
        'hh_id' => ['label' => 'User ID', 'sortable' => false],
        'quiz' => ['label' => 'Quiz', 'sortable' => true],
        'subject' => ['label' => 'Subject', 'sortable' => false],
        'reflection_type' => ['label' => 'Type', 'sortable' => true],
        'answers' => ['label' => 'Answers', 'sortable' => false],
        'detailed_answers' => ['label' => 'Details', 'sortable' => false],
        'created_at' => ['label' => 'Created At', 'sortable' => true],
        'updated_at' => ['label' => 'Updated At', 'sortable' => true],
    ];

    public function sortBy($column)
    {
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
        // build query with eager loading
        $query = QuizAnswers::with([
            'user:id,hh_id',
            'quiz:id,title,question_options',
            'activity:id,title',
            'subject'
        ]);

        // apply search filter
        if (!empty($this->search)) {
            $searchTerm = $this->search;
            
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('quiz', function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%');
                })
                ->orWhere(function ($q) use ($searchTerm) {
                    $q->where('reflection_type', 'like', '%' . $searchTerm . '%');
                    if (stripos('other', $searchTerm) !== false) {
                        $q->orWhereNull('reflection_type');
                    }
                })
                ->orWhereHas('user', function ($q) use ($searchTerm) {
                    $q->where('hh_id', 'like', '%' . $searchTerm . '%');
                })
                ->orWhereHas('activity', function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%');
                })
                ->orWhereHasMorph('subject', ['App\Models\Activity', 'App\Models\Module'], function ($q, $type) use ($searchTerm) {
                    if ($type === 'App\Models\Activity') {
                        $q->where('title', 'like', '%' . $searchTerm . '%');
                    } elseif ($type === 'App\Models\Module') {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    }
                })
                ->orWhere(function ($q) use ($searchTerm) {
                    if (stripos('activity', $searchTerm) !== false) {
                        $q->where('subject_type', 'App\Models\Activity');
                    } elseif (stripos('module', $searchTerm) !== false) {
                        $q->where('subject_type', 'App\Models\Module');
                    }
                });
            });
        }

        // sorting
        if ($this->sortColumn === 'quiz') {
            $query->leftJoin('quizzes', 'quiz_answers.quiz_id', '=', 'quizzes.id')
                ->leftJoin('activities', 'quizzes.activity_id', '=', 'activities.id')
                ->select('quiz_answers.*')
                ->orderBy('activities.title', $this->sortDirection);
        } else {
            $query->orderBy($this->sortColumn, $this->sortDirection);
        }

        $reflections = $query->paginate(10);

        return view('livewire.reflection-table', [
            'reflections' => $reflections
        ]);
    }

    public function formatAnswers(?array $answers): string
    {
        return QuizAnswerFormatter::formatAnswers($answers, 300);
    }
}


