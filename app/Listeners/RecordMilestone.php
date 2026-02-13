<?php

namespace App\Listeners;

use App\Events\MilestoneAchieved;
use App\Mail\MilestoneAchievedMail;
use App\Models\UserMilestone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class RecordMilestone implements ShouldQueue
{
    public function handle(MilestoneAchieved $event): void
    {
        // record milestone
        $milestone = UserMilestone::firstOrCreate(
            ['user_id' => $event->user->id, 'type' => $event->type],
            ['achieved_at' => now()]
        );
        
        // if it was not just created, return
        if (!$milestone->wasRecentlyCreated) {
            return;
        }

        // notify admin
        $adminEmail = config('mail.contact_email');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new MilestoneAchievedMail($event->user, $event->type));
            $milestone->update(['admin_notified_at' => now()]);
        }
    }
}
