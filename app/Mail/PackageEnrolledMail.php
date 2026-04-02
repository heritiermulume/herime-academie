<?php

namespace App\Mail;

use App\Models\ContentPackage;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PackageEnrolledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContentPackage $package,
        public ?Order $order = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Accès à votre pack — ' . $this->package->title . ' — Herime Académie',
        );
    }

    public function content(): Content
    {
        if (! $this->package->relationLoaded('contents')) {
            $this->package->load('contents');
        }

        $packUrl = route('customer.pack', $this->package);
        $courseCount = $this->package->contents->count();

        return new Content(
            view: 'emails.package-enrolled',
            with: [
                'package' => $this->package,
                'order' => $this->order,
                'packUrl' => $packUrl,
                'courseCount' => $courseCount,
                'logoUrl' => config('app.url') . '/images/logo-herime-academie.png',
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
