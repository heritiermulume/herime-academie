<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailSentNotification extends Notification
{
    use Queueable;

    protected $subject;
    protected $sentAt;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $subject, $sentAt = null)
    {
        $this->subject = $subject;
        $this->sentAt = $sentAt ?? now();
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
                    ->subject('Email envoyé')
                    ->line('Un email vous a été envoyé.')
                    ->line('Objet: ' . $this->subject)
                    ->action('Voir votre boîte mail', url('/'))
                    ->line('Merci d\'utiliser notre plateforme!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subject' => $this->subject,
            'message' => 'Un email vous a été envoyé avec l\'objet : "' . $this->subject . '". Veuillez consulter votre boîte mail.',
            'sent_at' => $this->sentAt->format('Y-m-d H:i:s'),
            'type' => 'email_sent',
        ];
    }
}
