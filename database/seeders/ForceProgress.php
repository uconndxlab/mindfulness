<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ForceProgress extends Seeder
{
    protected $progressService;
    public function __construct(ProgressService $progressService) {
        $this->progressService = $progressService;
    }
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order = 0;
        $email = config('mail.test_email');
        
        // update user progress up to the point of order
        $user = User::where('email', $email)->first();
        $user->quick_progress_warning = false;
        $user->last_day_completed_id = null;
        $user->save();

        $activities = Activity::where('order', '<=', $order)->get();

        // wipe existing progress
        DB::table('user_activity')->where('user_id', $user->id)->delete();
        DB::table('user_day')->where('user_id', $user->id)->delete();
        DB::table('user_module')->where('user_id', $user->id)->delete();

        // unlock first
        unlockFirst($user->id);

        foreach ($activities as $activity) {
            $this->progressService->completeActivity($user, $activity);
        }
    }
}
