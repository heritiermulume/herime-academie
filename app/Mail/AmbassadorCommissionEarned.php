<?php

namespace App\Mail;

use App\Models\AmbassadorCommission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AmbassadorCommissionEarned extends Mailable
{
    use Queueable, SerializesModels;

    public $commission;

    /**
     * Create a new message instance.
     */
    public function __construct(AmbassadorCommission $commission)
    {
        $this->commission = $commission;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Nouvelle commission gagnée - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->commission->load(['order', 'ambassador.user']);
        
        return new Content(
            view: 'emails.ambassador.commission-earned',
            with: [
                'commission' => $this->commission,
                'ambassador' => $this->commission->ambassador,
                'order' => $this->commission->order,
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
