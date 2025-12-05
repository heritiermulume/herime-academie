<?php

namespace App\Mail;

use App\Models\Ambassador;
use App\Models\AmbassadorApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AmbassadorApplicationApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $ambassador;
    public $application;
    public $promoCode;

    /**
     * Create a new message instance.
     */
    public function __construct(Ambassador $ambassador, AmbassadorApplication $application, $promoCode = null)
    {
        $this->ambassador = $ambassador;
        $this->application = $application;
        $this->promoCode = $promoCode;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Félicitations ! Vous êtes maintenant ambassadeur - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ambassador.application-approved',
            with: [
                'ambassador' => $this->ambassador,
                'application' => $this->application,
                'promoCode' => $this->promoCode,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
