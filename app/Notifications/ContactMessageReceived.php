<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ContactMessageReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ContactMessage $contactMessage
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
        return ['database'];
    }

    /**
     * Get the array representation of the notification (for database and navbar).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $messageUrl = route('admin.contact-messages.show', $this->contactMessage);

        return [
            'type' => 'contact_message_received',
            'title' => 'Nouveau message de contact',
            'contact_message_id' => $this->contactMessage->id,
            'name' => $this->contactMessage->name,
            'email' => $this->contactMessage->email,
            'subject' => $this->contactMessage->subject,
            'message' => Str::limit($this->contactMessage->message, 150),
            'message_full' => $this->contactMessage->message,
            'button_text' => 'Voir le message',
            'button_url' => $messageUrl,
            'url' => $messageUrl,
        ];
    }
}
