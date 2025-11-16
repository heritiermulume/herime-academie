<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CoursePublishedNotification extends Notification
{
    use Queueable;

    protected Course $course;

    public function __construct(Course $course)
    {
        $this->course = $course->loadMissing(['instructor', 'category']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $instructorName = $this->course->instructor?->name;
        $categoryName = $this->course->category?->name;

        $message = "Un nouveau cours « {$this->course->title} »";
        if ($instructorName) {
            $message .= " animé par {$instructorName}";
        }
        if ($categoryName) {
            $message .= " vient d'être publié dans la catégorie {$categoryName}.";
        } else {
            $message .= " vient d'être publié.";
        }

        return [
            'title' => 'Nouveau cours disponible',
            'excerpt' => $message,
            'type' => 'success',
            'button_text' => 'Découvrir le cours',
            'button_url' => route('courses.show', $this->course->slug),
            'course_id' => $this->course->id,
            'course_slug' => $this->course->slug,
        ];
    }
}












