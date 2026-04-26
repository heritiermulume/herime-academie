<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Paiement confirmé - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->order->load(array_merge(['user'], Order::eagerLoadOrderItemsWithPackages()));

        $copy = Order::paymentConfirmationCopy($this->order->orderItems);
        $purchasedTitles = Order::previewTitlesForOrderItems($this->order->orderItems);
        $purchasedSummary = match ($purchasedTitles->count()) {
            0 => 'contenu(x)',
            1 => $purchasedTitles->first(),
            2 => $purchasedTitles->join(' et '),
            default => $purchasedTitles->take(2)->implode(', ') . ' et ' . ($purchasedTitles->count() - 2) . ' autre(s) contenu(s)',
        };

        // Sécuriser le formatage de la date au cas où paid_at serait null ou mal formaté
        $paidAtText = null;
        try {
            if (!empty($this->order->paid_at)) {
                $paidAtText = $this->order->paid_at->timezone(config('app.timezone'))
                    ->format('d/m/Y à H:i');
            }
        } catch (\Throwable $e) {
            $paidAtText = null; // on masque la date si invalide
        }

        $orderUrl = route('orders.show', $this->order);

        return new Content(
            view: 'emails.payment-received',
            with: [
                'order' => $this->order,
                'orderUrl' => $orderUrl,
                'accessLabel' => $copy['access_label'],
                'actionText' => $copy['action_text'],
                'paidAtText' => $paidAtText,
                'logoUrl' => config('app.url') . '/images/logo-herime-academie.png',
                'purchasedSummary' => $purchasedSummary,
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

