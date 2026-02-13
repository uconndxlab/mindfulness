<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Activity;
use App\Models\Module;
use App\Enums\MilestoneType;

class BackfillUserMilestones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:backfill-milestones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill user milestones for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (User::all() as $user) {
            $user->milestones()->firstOrCreate(
                ['type' => MilestoneType::Registered],
                ['achieved_at' => $user->created_at]
            );

            $firstActivity = Activity::where('optional', false)->orderBy('order')->first();
            $userActivity = $user->activities()->where('activity_id', $firstActivity->id)->first()?->pivot;
            if ($userActivity && $userActivity->completed) {
                $user->milestones()->firstOrCreate(
                    ['type' => MilestoneType::FirstActivity],
                    ['achieved_at' => $userActivity->completed_at]
                );
            }

            foreach (Module::all() as $module) {
                $userModule = $user->modules()->where('module_id', $module->id)->first()?->pivot;
                if ($userModule && $userModule->completed) {
                    $user->milestones()->firstOrCreate(
                        ['type' => MilestoneType::forModule($module->order)],
                        ['achieved_at' => $userModule->completed_at]
                    );
                }
            }
        }
    }
}
