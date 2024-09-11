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
        //email after 7 days inactivity, then every 4 days after
        $inactive_time = Carbon::now()->subDays(7);
        $email_cooldown_time = Carbon::now()->subSeconds(4);
        $inactive_users = User::where('last_active_at', '<', $inactive_time)
                          ->where(function ($query) use ($email_cooldown_time) {
                              $query->whereNull('last_reminded_at')
                                    ->orWhere('last_reminded_at', '<', $email_cooldown_time);
                          })
                          ->get();

        foreach ($inactive_users as $user) {
            //do not send email to locked accounts
            if ($user->lock_access) {
                continue;
            }
            Mail::to($user->email)->send(new \App\Mail\InactivityReminder($user));
            $this->info('Reminder email sent to: ' . $user->email);

            //update user email timestamp
            $user->last_reminded_at = Carbon::now();
            $user->save();
        }
    }
}
