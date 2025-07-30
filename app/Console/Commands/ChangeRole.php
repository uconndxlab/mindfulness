<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class ChangeRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:role {email} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change a user\'s role by providing their email and the role.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $role = $this->argument('role');  

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User under '{$email}' not found");
            return 1;
        }

        if (!in_array($role, ['admin', 'user'])) {
            $this->error("Role '{$role}' not found");
            return 1;
        }
    
        $user->role = $role;
        $user->save();
        $this->info("Role '{$role}' assigned to user '{$user->email}'");
        return 0;
    }
}
