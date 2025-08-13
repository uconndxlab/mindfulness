<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ForceUserProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:force-progress {email} {--order=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a user\'s progress to a specific activity order';

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
        $email = $this->argument('email');
        $order = $this->option('order') ?? 0;

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return;
        }

        $this->info("Forcing progress for user: {$user->email} to order: {$order}");

        $user->quick_progress_warning = false;
        $user->last_day_completed_id = null;
        $user->save();

        $activities = Activity::where('order', '<=', $order)->orderBy('order')->get();

        // wipe existing progress
        DB::table('user_activity')->where('user_id', $user->id)->delete();
        DB::table('user_day')->where('user_id', $user->id)->delete();
        DB::table('user_module')->where('user_id', $user->id)->delete();

        // unlock first
        unlockFirst($user->id);

        foreach ($activities as $activity) {
            $this->progressService->completeActivity($user, $activity);
        }

        $this->info("Progress forced successfully.");
    }
}
