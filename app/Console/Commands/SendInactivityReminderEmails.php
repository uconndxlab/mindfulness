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
        $threshold = Carbon::now()->subDays(7);
        $inactive_users = User::where('last_active_at', '<', $threshold)
            ->where(function ($query) use ($threshold) {
                $query->whereNull('last_reminded_at')
                    ->orWhere('last_reminded_at', '<', $threshold);
            })
            ->get();

        $count = 0;
        foreach ($inactive_users as $user) {
            //do not send email to locked accounts
            if ($user->lock_access) {
                continue;
            }
            // send email
            Mail::to($user->email)->send(new \App\Mail\InactivityReminder($user));

            //update user email timestamp - set it to 12:00 EST to avoid delay
            $user->last_reminded_at = Carbon::now()->setTime(12, 0);
            $user->save();
            $count++;
        }
        $message = "Inactivity emails sent to { $count } users.";
        \Log::info($message);
        $this->info($message);
    }
}
