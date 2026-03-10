<?php

namespace App\Services;

use App\Mail\AdminWalletPayoutCompletedMail;
use App\Mail\AdminWalletPayoutFailedMail;
use App\Models\User;
use App\Models\WalletPayout;
use App\Notifications\AdminWalletPayoutCompleted;
use App\Notifications\AdminWalletPayoutFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class AdminWalletPayoutNotifier
{
    /**
     * Notifier les admins (email + notification in-app) qu'un payout wallet a réussi.
     * Détails : montant, compte destination, solde restant, etc.
     *
     * Ne lève jamais d'exception : un échec d'envoi (mail ou notification) est loggé
     * et n'empêche pas les autres envois (chaque canal et chaque admin est isolé).
     */
    public function notifyPayoutCompleted(WalletPayout $payout): void
    {
        try {
            $payout->load(['wallet.user']);
            $admins = User::admins()
                ->whereNotNull('email')
                ->where('is_active', true)
                ->get();

            if ($admins->isEmpty()) {
                return;
            }

            foreach ($admins as $admin) {
                try {
                    Mail::to($admin->email)->send(new AdminWalletPayoutCompletedMail($payout, $admin));
                } catch (\Throwable $e) {
                    Log::error('AdminWalletPayoutNotifier: erreur envoi email payout réussi', [
                        'wallet_payout_id' => $payout->id,
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                try {
                    Notification::sendNow($admin, new AdminWalletPayoutCompleted($payout));
                } catch (\Throwable $e) {
                    Log::error('AdminWalletPayoutNotifier: erreur notification payout réussi', [
                        'wallet_payout_id' => $payout->id,
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('AdminWalletPayoutNotifier: erreur globale notifyPayoutCompleted', [
                'wallet_payout_id' => $payout->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notifier les admins (email + notification in-app) qu'un payout wallet a échoué.
     * Détails : raison de l'échec, montant, compte destination, etc.
     *
     * Ne lève jamais d'exception : un échec d'envoi (mail ou notification) est loggé
     * et n'empêche pas les autres envois (chaque canal et chaque admin est isolé).
     */
    public function notifyPayoutFailed(WalletPayout $payout, ?string $reason = null): void
    {
        try {
            $payout->load(['wallet.user']);
            $failureReason = $reason ?? $payout->failure_reason ?? 'Raison non précisée';

            $admins = User::admins()
                ->whereNotNull('email')
                ->where('is_active', true)
                ->get();

            if ($admins->isEmpty()) {
                return;
            }

            foreach ($admins as $admin) {
                try {
                    Mail::to($admin->email)->send(new AdminWalletPayoutFailedMail($payout, $admin, $failureReason));
                } catch (\Throwable $e) {
                    Log::error('AdminWalletPayoutNotifier: erreur envoi email payout échoué', [
                        'wallet_payout_id' => $payout->id,
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                try {
                    Notification::sendNow($admin, new AdminWalletPayoutFailed($payout, $failureReason));
                } catch (\Throwable $e) {
                    Log::error('AdminWalletPayoutNotifier: erreur notification payout échoué', [
                        'wallet_payout_id' => $payout->id,
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('AdminWalletPayoutNotifier: erreur globale notifyPayoutFailed', [
                'wallet_payout_id' => $payout->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
