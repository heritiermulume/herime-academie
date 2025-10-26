<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Inscription confirmée - ' . $this->course->title)
            ->greeting('Félicitations !')
            ->line('Vous êtes maintenant inscrit au cours : ' . $this->course->title)
            ->line('Vous pouvez commencer à apprendre dès maintenant.')
            ->action('Commencer le cours', route('student.courses.learn', $this->course->slug))
            ->line('Merci d\'utiliser Herime Academie !');
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
            'instructor_name' => $this->course->instructor->name,
            'message' => 'Vous êtes maintenant inscrit au cours : ' . $this->course->title,
        ];
    }
}
