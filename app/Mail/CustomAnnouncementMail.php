<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class CustomAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $attachments;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $content, array $attachments = [])
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->attachments = $attachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-announcement',
            with: [
                'content' => $this->content,
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
        $attachments = [];

        foreach ($this->attachments as $attachmentPath) {
            if (file_exists(storage_path('app/' . $attachmentPath))) {
                $attachments[] = Attachment::fromPath(storage_path('app/' . $attachmentPath));
            }
        }

        return $attachments;
    }
}




