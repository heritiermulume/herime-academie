<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public ?User $admin = null
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Paiement effectué - ' . ($this->order->order_number ?? config('app.name')),
        );
    }

    public function content(): Content
    {
        $this->order->load(['user', 'orderItems.course', 'payments']);

        $adminUrl = null;
        try {
            $adminUrl = route('admin.orders.show', $this->order);
        } catch (\Throwable $e) {
            $adminUrl = null;
        }

        return new Content(
            view: 'emails.admin-payment-received',
            with: [
                'order' => $this->order,
                'adminUrl' => $adminUrl,
                'adminName' => $this->admin?->name,
                'logoUrl' => config('app.url') . '/images/logo-herime-academie.png',
            ],
        );
    }
}

