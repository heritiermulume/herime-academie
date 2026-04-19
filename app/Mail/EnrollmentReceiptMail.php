<?php

namespace App\Mail;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class EnrollmentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Course $course;

    /**
     * @param  string  $pdfContent  Contenu binaire du PDF du reçu
     */
    public function __construct(
        Course $course,
        private string $pdfContent
    ) {
        $this->course = $course;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Votre reçu d\'inscription - '.$this->course->title.' - Herime Académie',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollment-receipt',
            with: [
                'course' => $this->course,
                'courseUrl' => ($this->course->is_downloadable || ($this->course->is_in_person_program ?? false) || $this->course->isEnrollmentReceiptOnly())
                    ? route('contents.show', $this->course->slug)
                    : route('learning.course', $this->course->slug),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $filename = 'recu-inscription-'.Str::slug($this->course->title).'.pdf';

        return [
            Attachment::fromData(fn () => $this->pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
