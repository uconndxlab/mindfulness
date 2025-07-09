<?php

namespace App\Livewire;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    public $sortColumn = 'id';
    public $sortDirection = 'asc';

    public $columns = [
        'id' => ['label' => 'ID', 'sortable' => true],
        'name' => ['label' => 'Name', 'sortable' => true],
        'email' => ['label' => 'Email', 'sortable' => true],
        'role' => ['label' => 'Role', 'sortable' => false],
        'current_activity' => ['label' => 'Current Activity', 'sortable' => false],
        'last_active_at' => ['label' => 'Last Active', 'sortable' => true],
        'created_at' => ['label' => 'Joined', 'sortable' => true],
        'verified' => ['label' => 'Verified', 'sortable' => false],
        'last_reminder_at' => ['label' => 'Last Reminder', 'sortable' => true],
        'access' => ['label' => 'Access', 'sortable' => false],
        'actions' => ['label' => 'Actions', 'sortable' => false],
    ];

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at', 'lock_access', 'email_verified_at', 'last_active_at', 'last_reminder_at')
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate(10);

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
            $user->last_reminder_at = Carbon::now();
            $user->save();

            session()->flash('message', 'Reminder email sent to ' . $user->email);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send reminder email.');
        }
    }
}
