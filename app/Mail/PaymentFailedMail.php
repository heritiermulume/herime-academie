<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $failureReason;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, ?string $failureReason = null)
    {
        $this->order = $order;
        $this->failureReason = $failureReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Échec du paiement - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Charger les relations nécessaires
        $this->order->load(['orderItems.course', 'user']);

        $orderUrl = route('orders.show', $this->order);

        return new Content(
            view: 'emails.payment-failed',
            with: [
                'order' => $this->order,
                'orderUrl' => $orderUrl,
                'failureReason' => $this->failureReason,
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

