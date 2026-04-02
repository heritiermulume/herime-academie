<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
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
        // Ne pas utiliser 'mail' ici car l'email est envoyé directement lors de la confirmation de paiement (MonerooController / autres)
        // Cela évite d'envoyer l'email deux fois
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        // Sécuriser le formatage de la date au cas où paid_at serait null ou mal formaté
        $paidAtText = null;
        try {
            if (!empty($order->paid_at)) {
                $paidAtText = $order->paid_at->timezone(config('app.timezone'))
                    ->format('d/m/Y à H:i');
            }
        } catch (\Throwable $e) {
            $paidAtText = null; // on masque la date si invalide
        }
        
        $orderUrl = route('orders.show', $order);

        $order->load(Order::eagerLoadOrderItemsWithPackages());
        $copy = Order::paymentConfirmationCopy($order->orderItems);
        $accessMessage = 'Vous avez maintenant accès à tous les ' . $copy['access_label'] . ' que vous avez achetés. ' . $copy['action_text'];
        
        $mail = (new MailMessage)
            ->subject('Paiement confirmé - ' . config('app.name'))
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Nous sommes heureux de vous confirmer que votre paiement a bien été reçu.')
            ->line('**Numéro de commande :** ' . $order->order_number)
            ->line('**Montant :** ' . number_format($order->total, 2) . ' ' . $order->currency)
            ->action('Voir la commande', $orderUrl)
            ->line($accessMessage)
            ->line('Merci de votre confiance !');

        if ($paidAtText) {
            $mail->line('**Date :** ' . $paidAtText);
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->order->load(Order::eagerLoadOrderItemsWithPackages());
        $copy = Order::paymentConfirmationCopy($this->order->orderItems);

        return [
            'type' => 'payment_received',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->total,
            'currency' => $this->order->currency,
            'message' => 'Votre paiement de ' . number_format($this->order->total, 2) . ' ' . $this->order->currency . ' a été confirmé. ' . $copy['action_text'],
            'button_text' => 'Voir ma commande',
            'button_url' => route('orders.show', $this->order),
            'url' => route('orders.show', $this->order),
        ];
    }
}
