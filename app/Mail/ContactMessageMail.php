<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contactMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(ContactMessage $contactMessage)
    {
        $this->contactMessage = $contactMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjectLabels = [
            'inscription' => 'Inscription à un contenu',
            'paiement' => 'Paiement',
            'technique' => 'Problème technique',
            'support' => 'Support pédagogique',
            'partenariat' => 'Partenariat',
            'autre' => 'Autre',
        ];

        $subjectLabel = $subjectLabels[$this->contactMessage->subject] ?? ucfirst($this->contactMessage->subject);

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Nouveau message de contact - ' . $subjectLabel . ' - Herime Académie',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $subjectLabels = [
            'inscription' => 'Inscription à un contenu',
            'paiement' => 'Paiement',
            'technique' => 'Problème technique',
            'support' => 'Support pédagogique',
            'partenariat' => 'Partenariat',
            'autre' => 'Autre',
        ];

        return new Content(
            view: 'emails.contact-message',
            with: [
                'contactMessage' => $this->contactMessage,
                'subjectLabel' => $subjectLabels[$this->contactMessage->subject] ?? ucfirst($this->contactMessage->subject),
                'logoUrl' => config('app.url') . '/images/logo-herime-academie.png',
            ],
        );
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
