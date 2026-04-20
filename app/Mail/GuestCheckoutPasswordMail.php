<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestCheckoutPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Votre compte Herime Académie et votre mot de passe temporaire',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.guest-checkout-password',
            with: [
                'logoUrl' => config('app.url') . '/images/logo-herime-academie.png',
            ],
        );
    }
}
