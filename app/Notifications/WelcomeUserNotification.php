<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WelcomeUserNotification extends Notification
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // L'email/WhatsApp sont déjà gérés par CommunicationService.
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Bienvenue sur Herime Académie ! Nous sommes heureux de vous accompagner dans votre parcours.',
            'button_text' => 'Découvrir les contenus',
            'button_url' => route('contents.index'),
            'about_url' => route('about'),
            'type' => 'welcome_user',
        ];
    }
}
