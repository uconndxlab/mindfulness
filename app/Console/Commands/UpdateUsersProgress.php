<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProgressService;

class UpdateUsersProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'In the case of modifications to the order of activities, days, or modules, this command will update the progress of all users.';

    // progress service
    protected $progressService;
    public function __construct(ProgressService $progressService)
    {
        parent::__construct();
        $this->progressService = $progressService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->progressService->unlockAllUsersUpToLatestCompletion();
    }
}
