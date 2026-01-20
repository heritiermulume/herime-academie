<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ProviderPayout;

class ProviderPayoutReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ProviderPayout $payout
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
        // Ne pas utiliser 'mail' ici car l'email est envoyé directement dans MonerooPayoutService
        // Cela évite d'envoyer l'email deux fois
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $payout = $this->payout;
        
        // Charger les relations nécessaires
        if (!$payout->relationLoaded('course')) {
            $payout->load('course');
        }
        if (!$payout->relationLoaded('order')) {
            $payout->load('order');
        }

        // Sécuriser le formatage de la date
        $processedAtText = null;
        try {
            if (!empty($payout->processed_at)) {
                $processedAtText = $payout->processed_at->timezone(config('app.timezone'))
                    ->format('d/m/Y à H:i');
            }
        } catch (\Throwable $e) {
            $processedAtText = null;
        }

        $mail = (new MailMessage)
            ->subject('Paiement reçu - ' . config('app.name'))
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Nous sommes heureux de vous informer que votre paiement a été effectué avec succès.')
            ->line('**Montant reçu :** ' . number_format($payout->amount, 2) . ' ' . $payout->currency);

        if ($payout->course) {
            $mail->line('**Contenu :** ' . $payout->course->title);
        }

        if ($payout->order) {
            $mail->line('**Numéro de commande :** ' . $payout->order->order_number);
        }

        if ($payout->commission_amount > 0) {
            $mail->line('**Commission déduite :** ' . number_format($payout->commission_amount, 2) . ' ' . $payout->currency . ' (' . number_format($payout->commission_percentage, 2) . '%)');
        }

        if ($processedAtText) {
            $mail->line('**Date de traitement :** ' . $processedAtText);
        }

        $mail->line('Le montant a été transféré sur votre compte mobile money.')
            ->line('Merci pour votre contribution !');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $payout = $this->payout;
        
        // Charger les relations nécessaires
        if (!$payout->relationLoaded('course')) {
            $payout->load('course');
        }

        return [
            'type' => 'provider_payout_received',
            'payout_id' => $payout->id,
            'payout_id_external' => $payout->payout_id,
            'amount' => $payout->amount,
            'currency' => $payout->currency,
            'content_id' => $payout->content_id,
            'course_title' => $payout->course?->title,
            'message' => 'Vous avez reçu un paiement de ' . number_format($payout->amount, 2) . ' ' . $payout->currency . ($payout->course ? ' pour le contenu "' . $payout->course->title . '"' : ''),
            'url' => route('provider.dashboard'),
        ];
    }
}

