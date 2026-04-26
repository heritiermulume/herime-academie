<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Bienvenue sur Herime Académie',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-user',
            with: [
                'recipientName' => $this->recipientName,
                'logoUrl' => config('app.url').'/images/logo-herime-academie.png',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
