<?php

namespace App\Mail;

use App\Models\User;
use App\Models\WalletPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminWalletPayoutFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WalletPayout $payout,
        public ?User $admin = null,
        public string $failureReason = ''
    ) {
        $this->payout->load(['wallet.user']);
        if ($failureReason === '' && $this->payout->failure_reason) {
            $this->failureReason = (string) $this->payout->failure_reason;
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: sprintf(
                'Payout wallet échoué #%s - %s %s',
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
            view: 'emails.admin-wallet-payout-failed',
            with: [
                'payout' => $this->payout,
                'failureReason' => $this->failureReason,
                'adminUrl' => $adminUrl,
                'adminName' => $this->admin?->name,
                'logoUrl' => config('app.url') . '/images/logo-herime-academie.png',
            ],
        );
    }
}
