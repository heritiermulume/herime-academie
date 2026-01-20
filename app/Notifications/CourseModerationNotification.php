<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseModerationNotification extends Notification
{
    use Queueable;

    protected Course $course;
    protected string $status;
    protected ?string $notes;

    public function __construct(Course $course, string $status, ?string $notes = null)
    {
        $this->course = $course->loadMissing('category');
        $this->status = $status;
        $this->notes = $notes;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $payload = $this->resolveMessage();

        return [
            'title' => $payload['title'],
            'excerpt' => $payload['message'],
            'type' => $payload['type'],
            'button_text' => $this->status === 'approved' ? 'Voir le contenu' : 'Explorer les contenus',
            'button_url' => $this->status === 'approved'
                ? route('contents.show', $this->course->slug)
                : route('contents.index'),
            'course_id' => $this->course->id,
            'status' => $this->status,
        ];
    }

    protected function resolveMessage(): array
    {
        $courseTitle = $this->course->title;

        return match ($this->status) {
            'approved' => [
                'title' => 'Votre contenu est publié',
                'message' => "Le contenu « {$courseTitle} » a été approuvé et est maintenant visible sur la plateforme.",
                'type' => 'success',
            ],
            'rejected' => [
                'title' => 'Votre contenu a été rejeté',
                'message' => $this->notes
                    ? "Le contenu « {$courseTitle} » a été rejeté. Motif : {$this->notes}"
                    : "Le contenu « {$courseTitle} » a été rejeté. Merci d'apporter les modifications nécessaires.",
                'type' => 'error',
            ],
            'unpublished' => [
                'title' => 'Contenu dépublié',
                'message' => "Le contenu « {$courseTitle} » n'est plus visible sur la plateforme. {$this->notes}",
                'type' => 'warning',
            ],
            default => [
                'title' => 'Mise à jour de votre contenu',
                'message' => "Le statut du contenu « {$courseTitle} » a été mis à jour.",
                'type' => 'info',
            ],
        };
    }
}


