<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminPaymentReceived extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        // Email géré via AdminPaymentReceivedMail dans AdminPaymentNotifier
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $order = $this->order;

        // S'assurer que l'utilisateur est chargé pour afficher le client
        if (!$order->relationLoaded('user')) {
            $order->load('user');
        }

        $adminUrl = null;
        try {
            $adminUrl = route('admin.orders.show', $order);
        } catch (\Throwable $e) {
            $adminUrl = null;
        }

        $customerName = $order->user?->name ?? 'Client';
        $customerEmail = $order->user?->email;

        return [
            'type' => 'admin_payment_received',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'amount' => $order->total,
            'currency' => $order->currency,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'message' => "Paiement effectué : {$customerName} • {$order->order_number} • " . number_format((float) $order->total, 2) . ' ' . $order->currency,
            'button_text' => 'Voir la commande',
            'button_url' => $adminUrl,
            'url' => $adminUrl,
        ];
    }
}

