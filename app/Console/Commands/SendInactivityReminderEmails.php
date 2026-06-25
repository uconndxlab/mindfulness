<?php

namespace App\Console\Commands;

use App\Mail\InactivityContactNotification;
use App\Mail\InactivityReminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInactivityReminderEmails extends Command
{
    public const USER_MILESTONES = [3, 5, 7, 9, 11];
    public const CONTACT_MILESTONE = 12;
    public const ALL_MILESTONES = [3, 5, 7, 9, 11, 12];

    protected $signature = 'emails:send-inactivity-reminders';

    protected $description = 'Send inactivity reminder emails at 3/5/7/9/11 days; notify contact on day 12';

    public function handle(): int
    {
        $today = Carbon::now()->startOfDay();

        // only email users who are have access to the app
        $eligibleUsers = User::query()
            ->where('lock_access', false)
            ->whereNotNull('last_active_at')
            ->get(['id', 'hh_id', 'name', 'email', 'last_active_at', 'last_inactivity_reminder_day']);

        $inactiveCounts = array_fill_keys(self::ALL_MILESTONES, 0);
        $queuedUserReminders = 0;
        $queuedContactNotifications = 0;

        foreach ($eligibleUsers as $user) {
            $inactiveDays = $user->last_active_at->copy()->startOfDay()->diffInDays($today);

            // increment inactive counts for all milestones
            foreach (self::ALL_MILESTONES as $threshold) {
                if ($inactiveDays >= $threshold) {
                    $inactiveCounts[$threshold]++;
                }
            }

            // skip if user has not been inactive for at least 3 days
            if ($inactiveDays < self::USER_MILESTONES[0]) {
                continue;
            }

            // skip if admin has already been notified
            if (($user->last_inactivity_reminder_day ?? 0) >= self::CONTACT_MILESTONE) {
                continue;
            }

            // get next milestone for user
            $nextMilestone = $this->nextMilestoneFor($inactiveDays, $user->last_inactivity_reminder_day);
            if ($nextMilestone === null) {
                continue;
            }

            if ($nextMilestone === self::CONTACT_MILESTONE && $this->inactivityAlertRecipients() === []) {
                Log::warning('Skipping day-12 inactivity alert for user '.$user->id.': no admin recipients configured.');
                continue;
            }

            $user->update([
                'last_inactivity_reminder_day' => $nextMilestone,
                'last_reminded_at' => Carbon::now(),
            ]);

            if ($nextMilestone === self::CONTACT_MILESTONE) {
                Mail::to($this->inactivityAlertRecipients())->queue(
                    new InactivityContactNotification($user, $inactiveDays)
                );
                $queuedContactNotifications++;
            } else {
                Mail::to($user->email)->queue(new InactivityReminder($user));
                $queuedUserReminders++;
            }
        }

        // log summary
        $summary = sprintf(
            'Inactivity reminders: inactive >=3: %d, >=5: %d, >=7: %d, >=9: %d, >=11: %d, >=12: %d. Queued %d user reminder(s), %d contact notification(s).',
            $inactiveCounts[3],
            $inactiveCounts[5],
            $inactiveCounts[7],
            $inactiveCounts[9],
            $inactiveCounts[11],
            $inactiveCounts[12],
            $queuedUserReminders,
            $queuedContactNotifications,
        );

        Log::info($summary);
        $this->info($summary);

        return self::SUCCESS;
    }

    private function nextMilestoneFor(int $inactiveDays, ?int $lastMilestone): ?int
    {
        $lastMilestone ??= 0;

        foreach (self::ALL_MILESTONES as $milestone) {
            if ($inactiveDays >= $milestone && $lastMilestone < $milestone) {
                return $milestone;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function inactivityAlertRecipients(): array
    {
        $configured = config('mail.inactivity_alert_emails', []);
        if ($configured !== []) {
            return $configured;
        }

        $contactEmail = config('mail.contact_email');
        if (is_string($contactEmail) && $contactEmail !== '') {
            return [$contactEmail];
        }

        return [];
    }
}
