<?php

namespace App\Mail;

use App\Models\User;
use App\Models\WalletPayout;
use App\Support\RecipientDisplayName;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminWalletPayoutCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WalletPayout $payout,
        public User $admin
    ) {
        $this->payout->load(['wallet.user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: sprintf(
                'Payout wallet réussi #%s - %s %s',
                $this->payout->id,
                number_format((float) $this->payout->amount, 2),
                $this->payout->currency ?? ''
            ),
        );
    }

    public function content(): Content
    {
        $adminUrl = null;
        try {
            $adminUrl = route('admin.wallet.payments');
        } catch (\Throwable $e) {
            // ignore
        }

        return new Content(
            view: 'emails.admin-wallet-payout-completed',
            with: [
                'payout' => $this->payout,
                'adminUrl' => $adminUrl,
                'adminName' => RecipientDisplayName::resolve($this->admin->name ?? null, $this->admin->email ?? null),
                'logoUrl' => config('app.url') . '/images/logo-herime-academie.png',
            ],
        );
    }
}
