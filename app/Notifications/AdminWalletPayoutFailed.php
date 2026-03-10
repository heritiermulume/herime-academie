<?php

namespace App\Notifications;

use App\Models\WalletPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminWalletPayoutFailed extends Notification
{
    use Queueable;

    public function __construct(public WalletPayout $payout, public string $failureReason)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $payout = $this->payout;
        if (!$payout->relationLoaded('wallet')) {
            $payout->load(['wallet.user']);
        }
        $destinationLabel = $payout->method === 'manual'
            ? ($payout->description ?? 'Retrait manuel')
            : trim(($payout->phone ?? '') . ' ' . ($payout->method ?? '') . ' ' . ($payout->country ? '(' . $payout->country . ')' : ''));
        if ($destinationLabel === '') {
            $destinationLabel = '—';
        }
        $adminUrl = null;
        try {
            $adminUrl = route('admin.wallet.payments');
        } catch (\Throwable $e) {
        }
        return [
            'type' => 'admin_wallet_payout_failed',
            'wallet_payout_id' => $payout->id,
            'amount' => (string) $payout->amount,
            'currency' => $payout->currency ?? '—',
            'destination' => $destinationLabel,
            'failure_reason' => $this->failureReason,
            'message' => 'Payout échoué : ' . number_format((float) $payout->amount, 2) . ' ' . ($payout->currency ?? '') . '. Raison : ' . $this->failureReason,
            'button_text' => 'Voir les paiements',
            'button_url' => $adminUrl,
            'url' => $adminUrl,
        ];
    }
}
