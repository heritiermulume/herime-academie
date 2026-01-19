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
        // Charger les relations nécessaires
        $this->order->load(['orderItems.course', 'user']);

        // Déterminer le libellé adapté selon le type de contenus achetés
        $orderItems = $this->order->orderItems;
        $hasDownloadable = $orderItems->contains(function ($item) {
            return $item->course && $item->course->is_downloadable;
        });
        $hasNonDownloadable = $orderItems->contains(function ($item) {
            return $item->course && !$item->course->is_downloadable;
        });

        if ($hasDownloadable && !$hasNonDownloadable) {
            // Uniquement des produits digitaux / téléchargeables
            $accessLabel = 'produits digitaux';
        } elseif (!$hasDownloadable && $hasNonDownloadable) {
            // Uniquement des cours classiques
            $accessLabel = 'cours';
        } elseif ($hasDownloadable && $hasNonDownloadable) {
            // Panier mixte
            $accessLabel = 'cours et produits digitaux';
        } else {
            // Fallback générique
            $accessLabel = 'contenus';
        }

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
                'accessLabel' => $accessLabel,
                'paidAtText' => $paidAtText,
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

