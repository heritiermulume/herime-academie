<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ContentRatingRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Course $course,
        public string $ratingUrl,
        public bool $usePurchaseWording = true
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
        $trimmed = trim((string) $this->user->name);
        $greetingName = Str::before($trimmed, ' ');
        if ($greetingName === '') {
            $greetingName = $trimmed ?: 'cher apprenant';
        }

        return new Content(
            view: 'emails.content-rating-request',
            with: [
                'user' => $this->user,
                'course' => $this->course,
                'ratingUrl' => $this->ratingUrl,
                'greetingName' => $greetingName,
                'usePurchaseWording' => $this->usePurchaseWording,
                'logoUrl' => config('app.url').'/images/logo-herime-academie.png',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
