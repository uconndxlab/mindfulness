<?php

namespace App\Livewire;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class UserTable extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';
    public $sortColumn = 'created_at';
    public $sortDirection = 'desc';

    public $columns = [
        'hh_id' => ['label' => 'ID', 'sortable' => true],
        'name' => ['label' => 'Name', 'sortable' => true],
        'email' => ['label' => 'Email', 'sortable' => true],
        'role' => ['label' => 'Role', 'sortable' => false],
        'current_activity' => ['label' => 'Current Activity', 'sortable' => false],
        'last_active_at' => ['label' => 'Last Active', 'sortable' => true],
        'num_favorites' => ['label' => 'Number of Favorites', 'sortable' => false],
        'created_at' => ['label' => 'Joined', 'sortable' => true],
        'verified' => ['label' => 'Verified', 'sortable' => false],
        'last_reminded_at' => ['label' => 'Last Reminder', 'sortable' => true],
        'access' => ['label' => 'Access', 'sortable' => false],
        'actions' => ['label' => 'Actions', 'sortable' => false],
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
        // query
        $usersQuery = User::select('id', 'hh_id', 'name', 'email', 'role', 'created_at', 'lock_access', 'email_verified_at', 'last_active_at', 'last_reminded_at')
            ->with(['favoritedActivities' => fn($query) => $query->orderBy('order', 'asc'), 'favoritedActivities.day.module'])
            ->orderBy($this->sortColumn, $this->sortDirection);

        // search - cannot query search because of current activity
        if (!empty($this->search)) {
            $allUsers = $usersQuery->get();
            $filteredUsers = $allUsers->filter(function ($user) {
                $currentActivity = $user->currentActivity();
                $activityTitle = $currentActivity ? $currentActivity->title : '';

                return str_contains(strtolower($user->name), strtolower($this->search)) ||
                    str_contains(strtolower($user->hh_id), strtolower($this->search)) ||
                    str_contains(strtolower($user->email), strtolower($this->search)) ||
                    str_contains(strtolower($user->role), strtolower($this->search)) ||
                    str_contains(strtolower($activityTitle), strtolower($this->search));
            });
            $users = new \Illuminate\Pagination\LengthAwarePaginator(
                $filteredUsers->forPage($this->getPage(), 10),
                $filteredUsers->count(),
                10,
                $this->getPage(),
                ['path' => request()->url()]
            );
        } else {
            $users = $usersQuery->paginate(10);
        }


        return view('livewire.user-table', [
            'users' => $users
        ]);
    }

    public function toggleAccess($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->lock_access = !$user->lock_access;
            $user->save();
            session()->flash('message', 'Access updated for ' . $user->email);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to change access.');
        }
    }

    public function sendReminder($userId)
    {
        try {
            $user = User::findOrFail($userId);

            if ($user->lock_access) {
                session()->flash('error', 'User access is locked.');
                return;
            }

            if (!$user->canSendReminder()) {
                session()->flash('error', 'User has been active or reminded within the limit.');
                return;
            }

            Mail::to($user->email)->send(new \App\Mail\InactivityReminder($user));
            $user->last_reminded_at = Carbon::now();
            $user->save();

            session()->flash('message', 'Reminder email sent to ' . $user->email);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send reminder email.');
        }
    }
}
