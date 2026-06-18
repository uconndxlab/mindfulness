<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InactivityContactNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $inactiveDays,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'User inactive for '.$this->inactiveDays.' days',
        );
    }

    public function build()
    {
        return $this->view('emails.inactivity-contact-notification')
            ->with([
                'user' => $this->user,
                'inactiveDays' => $this->inactiveDays,
            ]);
    }
}
