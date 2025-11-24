<?php

namespace App\Mail;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseEnrolledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $course;

    /**
     * Create a new message instance.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Inscription confirmée - ' . $this->course->title . ' - Herime Académie',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Charger les relations nécessaires
        $this->course->load(['instructor', 'category']);

        // Déterminer l'URL appropriée selon le type de cours
        // Pour les cours téléchargeables, rediriger vers la page de détails du cours
        // Pour les cours normaux, rediriger vers la page learning
        if ($this->course->is_downloadable) {
            $courseUrl = route('courses.show', $this->course->slug);
            $buttonText = 'Télécharger le cours maintenant';
        } else {
            $courseUrl = route('learning.course', $this->course->slug);
            $buttonText = 'Commencer le cours maintenant';
        }

        return new Content(
            view: 'emails.course-enrolled',
            with: [
                'course' => $this->course,
                'courseUrl' => $courseUrl,
                'buttonText' => $buttonText,
                'logoUrl' => config('app.url') . '/images/logo-herime-academie.png',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}


