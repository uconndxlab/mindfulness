<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class BackfillUserHhIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:backfill-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill hh_id for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('hh_id')->get();

        if ($users->isEmpty()) {
            $this->info('No users need a hh_id. All set!');
            return 0;
        }

        $this->withProgressBar($users, function (User $user) {
            $user->hh_id = User::generateHhId();
            $user->saveQuietly();
        });

        $this->newLine();
        $this->info('Successfully backfilled public IDs for ' . $users->count() . ' users.');
        return 0;
    }
}
