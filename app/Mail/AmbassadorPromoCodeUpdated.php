<?php

namespace App\Mail;

use App\Models\Ambassador;
use App\Models\AmbassadorPromoCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AmbassadorPromoCodeUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $ambassador;
    public $promoCode;
    public $oldPromoCode;
    public $isNewCode;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Ambassador $ambassador, 
        AmbassadorPromoCode $promoCode, 
        $oldPromoCode = null,
        $isNewCode = false
    ) {
        $this->ambassador = $ambassador;
        $this->promoCode = $promoCode;
        $this->oldPromoCode = $oldPromoCode;
        $this->isNewCode = $isNewCode;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isNewCode 
            ? 'Nouveau code promo généré - ' . config('app.name')
            : 'Code promo mis à jour - ' . config('app.name');
            
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ambassador.promo-code-updated',
            with: [
                'ambassador' => $this->ambassador,
                'promoCode' => $this->promoCode,
                'oldPromoCode' => $this->oldPromoCode,
                'isNewCode' => $this->isNewCode,
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










