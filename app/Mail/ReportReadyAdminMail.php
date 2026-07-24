<?php

namespace App\Mail;

use App\Models\CompletionReport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportReadyAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public CompletionReport $report,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Completion report ready - ' . $this->user->hh_id,
        );
    }

    public function build()
    {
        $completedAt = $this->user->milestones()
            ->where('type', \App\Enums\MilestoneType::Module4)
            ->value('achieved_at');

        return $this->view('emails.report-ready-admin')
            ->with([
                'user' => $this->user,
                'completedAt' => $completedAt,
                'reportsUrl' => route('admin.completion-reports', ['hh_id' => $this->user->hh_id]),
            ]);
    }
}
