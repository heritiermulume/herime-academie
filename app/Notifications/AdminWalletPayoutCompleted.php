<?php

namespace App\Notifications;

use App\Models\WalletPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminWalletPayoutCompleted extends Notification
{
    use Queueable;

    public function __construct(public WalletPayout $payout)
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
        $remainingBalance = $payout->wallet ? (string) $payout->wallet->available_balance : '—';
        $adminUrl = null;
        try {
            $adminUrl = route('admin.wallet.payments');
        } catch (\Throwable $e) {
        }
        return [
            'type' => 'admin_wallet_payout_completed',
            'wallet_payout_id' => $payout->id,
            'amount' => (string) $payout->amount,
            'currency' => $payout->currency ?? '—',
            'destination' => $destinationLabel,
            'remaining_balance' => $remainingBalance,
            'message' => 'Payout réussi : ' . number_format((float) $payout->amount, 2) . ' ' . ($payout->currency ?? '') . ' vers ' . $destinationLabel . '. Solde restant : ' . number_format((float) $remainingBalance, 2) . ' ' . ($payout->currency ?? '') . '.',
            'button_text' => 'Voir les paiements',
            'button_url' => $adminUrl,
            'url' => $adminUrl,
        ];
    }
}
