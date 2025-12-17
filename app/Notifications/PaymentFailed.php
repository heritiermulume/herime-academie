<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification envoyée lorsqu'un paiement échoue
 * 
 * Cette notification est envoyée dans tous les cas d'échec:
 * - Échec d'initialisation
 * - Solde insuffisant
 * - Carte rejetée
 * - Paiement annulé
 * - Délai expiré
 * - Erreur technique
 */
class PaymentFailed extends Notification
{
    use Queueable;

    protected Order $order;
    protected ?string $failureReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, ?string $failureReason = null)
    {
        $this->order = $order;
        $this->failureReason = $failureReason ?? 'Le paiement n\'a pas pu être complété';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Échec de paiement - Commande #' . $this->order->order_number)
                    ->error()
                    ->greeting('Bonjour ' . $notifiable->name . ',')
                    ->line('Votre paiement pour la commande #' . $this->order->order_number . ' a échoué.')
                    ->line('**Raison:** ' . $this->failureReason)
                    ->line('**Montant:** ' . \App\Helpers\CurrencyHelper::formatWithSymbol($this->order->total))
                    ->line('Vous pouvez réessayer le paiement en retournant à votre panier.')
                    ->action('Retour au panier', url('/cart'))
                    ->line('Si le problème persiste, veuillez contacter notre support.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_failed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->total,
            'currency' => $this->order->currency,
            'failure_reason' => $this->failureReason,
            'message' => 'Votre paiement pour la commande #' . $this->order->order_number . ' a échoué. Raison: ' . $this->failureReason,
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'action_url' => route('cart.index'),
            'action_text' => 'Retour au panier',
        ];
    }

    /**
     * Get the notification's database type (for polymorphic notification system)
     */
    public function databaseType(): string
    {
        return 'payment_failed';
    }
}

