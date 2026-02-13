<?php

namespace App\Mail;

use App\Enums\MilestoneType;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MilestoneAchievedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public MilestoneType $type
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Milestone Achieved: ' . $this->type->label(),
        );
    }

    public function build()
    {
        return $this->view('emails.milestone-achieved')
            ->with([
                'user' => $this->user,
                'milestoneType' => $this->type,
                'milestoneLabel' => $this->type->label(),
            ]);
    }
}
