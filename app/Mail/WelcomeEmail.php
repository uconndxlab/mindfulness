<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to '.config('app.name').' - Important Resources',
        );
    }

    public function build()
    {
        $mail = $this->view('emails.welcome')
            ->with([
                'user' => $this->user,
            ]);

        $disk = Storage::disk('public');
        $attachments = [
            config('welcome.letter_pdf') => 'Welcome Letter.pdf',
            config('welcome.workbook_pdf') => 'Workbook.pdf',
        ];

        foreach ($attachments as $path => $as) {
            if ($path && $disk->exists($path)) {
                $mail->attach($disk->path($path), [
                    'as' => $as,
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}
