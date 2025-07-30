<?php

namespace App\Mail;

use App\Models\Email_Body;
use App\Models\Email_Subject;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InactivityReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $subject, $body;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->subject = Email_Subject::where('type', 'reminder')->inRandomOrder()->first()->subject;
        $this->body = Email_Body::where('type', 'reminder')->inRandomOrder()->first()->body;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function build()
    {

        return $this->view('emails.inactivity-reminder')
                    ->with(['user' => $this->user, 'body' => $this->body]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
