<?php

namespace App\Mail;

use App\Models\ContentPackage;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PackageEnrollmentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContentPackage $package,
        private string $pdfContent,
        public ?Order $order = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Votre reçu — Pack ' . $this->package->title . ' — Herime Académie',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.package-enrollment-receipt',
            with: [
                'package' => $this->package,
                'packUrl' => route('customer.pack', $this->package),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $filename = 'recu-pack-' . Str::slug($this->package->title) . '.pdf';

        return [
            Attachment::fromData(fn () => $this->pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
