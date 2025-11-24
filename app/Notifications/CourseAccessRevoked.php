<?php

namespace App\Notifications;

use App\Models\Course;
use App\Mail\CourseAccessRevokedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseAccessRevoked extends Notification
{
    use Queueable;

    protected $course;

    /**
     * Create a new notification instance.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Ne pas utiliser 'mail' ici car l'email est envoyé directement dans AdminController::unenrollUser()
        // Cela évite d'envoyer l'email deux fois
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        // Utiliser le Mailable personnalisé pour l'email HTML avec la charte graphique
        return new CourseAccessRevokedMail($this->course);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'course_slug' => $this->course->slug,
            'instructor_name' => $this->course->instructor->name ?? 'N/A',
            'message' => 'Votre accès au cours : ' . $this->course->title . ' a été retiré',
        ];
    }
}

