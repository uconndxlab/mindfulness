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
        $order = 9;
        $email = 'zoro@op.com';
        
        // update user progress up to the point of order
        $user = User::where('email', $email)->first();
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
