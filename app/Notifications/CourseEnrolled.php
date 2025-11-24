<?php

namespace App\Notifications;

use App\Models\Course;
use App\Mail\CourseEnrolledMail;
use App\Services\EmailService;
use App\Notifications\EmailSentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CourseEnrolled extends Notification
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
        // Ne pas utiliser 'mail' ici car l'email est envoyé directement dans Enrollment::sendEnrollmentNotifications()
        // Cela évite d'envoyer l'email deux fois
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        try {
            // Charger les relations nécessaires si elles ne sont pas déjà chargées
            if (!$this->course->relationLoaded('instructor')) {
                $this->course->load('instructor');
            }
            if (!$this->course->relationLoaded('category')) {
                $this->course->load('category');
            }
            
            // Utiliser le Mailable personnalisé pour l'email HTML avec la charte graphique
            $mailable = new CourseEnrolledMail($this->course);
            
            \Log::info("CourseEnrolled::toMail() appelé", [
                'user_id' => $notifiable->id,
                'user_email' => $notifiable->email,
                'course_id' => $this->course->id,
                'course_title' => $this->course->title,
            ]);
            
            return $mailable;
        } catch (\Exception $e) {
            \Log::error("Erreur dans CourseEnrolled::toMail()", [
                'user_id' => $notifiable->id ?? null,
                'user_email' => $notifiable->email ?? null,
                'course_id' => $this->course->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Charger les relations nécessaires si elles ne sont pas déjà chargées
        if (!$this->course->relationLoaded('instructor')) {
            $this->course->load('instructor');
        }

        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'course_slug' => $this->course->slug,
            'instructor_name' => $this->course->instructor?->name ?? 'Instructeur inconnu',
            'message' => 'Vous êtes maintenant inscrit au cours : ' . $this->course->title,
            'type' => 'course_enrolled',
        ];
    }
}
