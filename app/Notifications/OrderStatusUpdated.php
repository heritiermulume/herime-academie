<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    protected Order $order;
    protected string $status;
    protected ?string $message;

    public function __construct(Order $order, string $status, ?string $message = null)
    {
        $this->order = $order;
        $this->status = $status;
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $payload = $this->resolveMessage($this->status);

        if ($this->message) {
            $payload['message'] .= ' ' . $this->message;
        }

        return [
            'title' => $payload['title'],
            'excerpt' => $payload['message'],
            'type' => $payload['type'],
            'button_text' => 'Voir ma commande',
            'button_url' => route('orders.show', $this->order),
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->status,
            'total' => $this->order->total_amount ?? $this->order->total,
            'currency' => $this->order->currency,
        ];
    }

    protected function resolveMessage(string $status): array
    {
        $orderNumber = $this->order->order_number;

        return match ($status) {
            'pending' => [
                'title' => "Commande enregistrée (#{$orderNumber})",
                'message' => "Votre commande a bien été créée. Nous traitons votre paiement.",
                'type' => 'info',
            ],
            'confirmed' => [
                'title' => "Commande confirmée (#{$orderNumber})",
                'message' => "Votre commande a été confirmée par notre équipe. Vous aurez bientôt accès à vos cours.",
                'type' => 'success',
            ],
            'paid' => [
                'title' => "Paiement reçu (#{$orderNumber})",
                'message' => "Merci ! Nous avons reçu votre paiement. Vos formations sont maintenant disponibles.",
                'type' => 'success',
            ],
            'completed' => [
                'title' => "Commande finalisée (#{$orderNumber})",
                'message' => "Votre commande est terminée. Bon apprentissage !",
                'type' => 'success',
            ],
            'cancelled' => [
                'title' => "Commande annulée (#{$orderNumber})",
                'message' => "Votre commande a été annulée. Contactez-nous si vous avez besoin d’assistance.",
                'type' => 'warning',
            ],
            default => [
                'title' => "Mise à jour de la commande (#{$orderNumber})",
                'message' => "Le statut de votre commande a été mis à jour.",
                'type' => 'info',
            ],
        };
    }
}











