<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;

class SendInactivityReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-inactivity-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to users who have been inactive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // email after 7 days inactivity with 7 day cooldown
        $inactiveTime = Carbon::now()->subDays(7);
        $emailCooldown = Carbon::now()->subDays(7);
        $inactive_users = User::where('last_active_at', '<', $inactiveTime)
            ->where(function ($query) use ($emailCooldown) {
                $query->whereNull('last_reminder_send_at')
                    ->orWhere('last_reminder_send_at', '<', $emailCooldown);
            })
            ->get();

        foreach ($inactive_users as $user) {
            //do not send email to locked accounts
            if ($user->lock_access) {
                continue;
            }
            // send email
            Mail::to($user->email)->queue(new \App\Mail\InactivityReminder($user));

            //update user email timestamp
            $user->last_reminded_at = Carbon::now();
            $user->save();
        }
        $this->info("Inactivity emails sent to {$inactive_users->count()} users.");
    }
}
