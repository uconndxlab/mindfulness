<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    public function render()
    {
        $users = User::select('id', 'name', 'email', 'created_at', 'lock_access', 'email_verified_at', 'last_active_at')
            ->paginate(10);

        return view('livewire.user-table', [
            'users' => $users
        ]);
    }
}
