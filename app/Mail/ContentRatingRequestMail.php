<?php

namespace App\Mail;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContentRatingRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Course $course,
        public string $ratingUrl
    ) {
        $this->course->loadMissing(['provider', 'category']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Votre avis compte — '.$this->course->title.' — Herime Académie',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.content-rating-request',
            with: [
                'course' => $this->course,
                'ratingUrl' => $this->ratingUrl,
                'logoUrl' => config('app.url').'/images/logo-herime-academie.png',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
