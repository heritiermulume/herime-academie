<?php

namespace App\Mail;

use App\Models\ProviderPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderPayoutReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payout;

    /**
     * Create a new message instance.
     */
    public function __construct(ProviderPayout $payout)
    {
        $this->payout = $payout;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Paiement reçu - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Charger les relations nécessaires
        $this->payout->load(['course', 'order', 'provider']);

        // Sécuriser le formatage de la date
        $processedAtText = null;
        try {
            if (!empty($this->payout->processed_at)) {
                $processedAtText = $this->payout->processed_at->timezone(config('app.timezone'))
                    ->format('d/m/Y à H:i');
            }
        } catch (\Throwable $e) {
            $processedAtText = null;
        }

        return new Content(
            view: 'emails.provider-payout-received',
            with: [
                'payout' => $this->payout,
                'processedAtText' => $processedAtText,
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

