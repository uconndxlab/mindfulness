<?php

namespace App\Listeners;

use App\Enums\MilestoneType;
use App\Events\MilestoneAchieved;
use App\Mail\MilestoneAchievedMail;
use App\Models\UserMilestone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class RecordMilestone implements ShouldQueue
{
    public function handle(MilestoneAchieved $event): void
    {
        $type = MilestoneType::from($event->milestoneType);

        // record milestone
        $milestone = UserMilestone::firstOrCreate(
            ['user_id' => $event->user->id, 'type' => $type],
            ['achieved_at' => now()]
        );
        
        // if it was not just created, return
        if (!$milestone->wasRecentlyCreated) {
            return;
        }

        // notify admin
        $adminEmail = config('mail.contact_email');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new MilestoneAchievedMail($event->user, $type));
            $milestone->update(['admin_notified_at' => now()]);
        }
    }
}
