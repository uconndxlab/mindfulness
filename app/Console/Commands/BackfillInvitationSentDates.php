<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invitation;

class BackfillInvitationSentDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitations:backfill-sent-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill last_sent_at and resend_count for all invitations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invitations = Invitation::all();
        foreach ($invitations as $invitation) {
            if (!$invitation->last_sent_at) {
                $invitation->last_sent_at = $invitation->created_at;
            }
            if (!$invitation->resend_count) {
                $invitation->resend_count = 0;
            }
            $invitation->save();
        }
    }
}
