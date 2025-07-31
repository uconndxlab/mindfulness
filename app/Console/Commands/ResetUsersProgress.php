<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ResetUsersProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:reset-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset progress for all users and clears session data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();

        DB::table('user_activity')->delete();
        DB::table('user_day')->delete();
        DB::table('user_module')->delete();
        DB::table('sessions')->delete();

        foreach ($users as $user) {
            // start user
            unlockFirst($user->id);
            $user->quick_progress_warning = false;
            $user->last_day_completed_id = null;
            $user->save();
        }

        $this->info("Progress reset for all users");
        return 0;
    }
}
