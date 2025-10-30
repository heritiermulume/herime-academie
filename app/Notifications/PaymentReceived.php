<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class PaymentReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        
        return (new MailMessage)
            ->subject('Paiement confirmé - ' . config('app.name'))
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Nous sommes heureux de vous confirmer que votre paiement a bien été reçu.')
            ->line('**Numéro de commande :** ' . $order->order_number)
            ->line('**Montant :** ' . number_format($order->total, 2) . ' ' . $order->currency)
            ->line('**Date :** ' . $order->paid_at->format('d/m/Y à H:i'))
            ->action('Voir mes commandes', url('/orders'))
            ->line('Vous avez maintenant accès à tous les cours que vous avez achetés.')
            ->line('Merci de votre confiance !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_received',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->total,
            'currency' => $this->order->currency,
            'message' => 'Votre paiement de ' . number_format($this->order->total, 2) . ' ' . $this->order->currency . ' a été confirmé.',
        ];
    }
}
