<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PendingPaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Finalisez votre commande - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing(array_merge(['user'], Order::eagerLoadOrderItemsWithPackages()));
        $orderLines = Order::previewTitlesForOrderItems($this->order->orderItems)->all();
        $linkTtlMinutes = max(5, (int) env('ORDER_PENDING_REMINDER_LINK_TTL_MIN', 1440));
        $orderUrl = URL::temporarySignedRoute(
            'orders.restore-cart.signed',
            now()->addMinutes($linkTtlMinutes),
            ['order' => $this->order->id]
        );

        return new Content(
            view: 'emails.pending-payment-reminder',
            with: [
                'order' => $this->order,
                'orderUrl' => $orderUrl,
                'orderLines' => $orderLines,
                'logoUrl' => config('app.url').'/images/logo-herime-academie.png',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
