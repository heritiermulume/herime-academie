<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletHold;
use Illuminate\Support\Facades\Log;

class WalletAutoReleaseService
{
    /**
     * Vérifier et libérer automatiquement les fonds expirés pour un wallet spécifique
     */
    public function releaseExpiredHoldsForWallet(Wallet $wallet): int
    {
        // Vérifier si la libération automatique est activée
        if (!\App\Models\Setting::get('wallet_auto_release_enabled', true)) {
            return 0;
        }

        $releasedCount = 0;
        $releasedAmount = 0;

        // Récupérer tous les holds expirés pour ce wallet
        $expiredHolds = $wallet->holds()
            ->where('status', 'held')
            ->where('held_until', '<=', now())
            ->get();

        if ($expiredHolds->isEmpty()) {
            return 0;
        }

        foreach ($expiredHolds as $hold) {
            try {
                if ($hold->release()) {
                    $releasedCount++;
                    $releasedAmount += $hold->amount;
                    
                    Log::info('Hold libéré automatiquement (navigation utilisateur)', [
                        'hold_id' => $hold->id,
                        'wallet_id' => $wallet->id,
                        'user_id' => $wallet->user_id,
                        'amount' => $hold->amount,
                        'currency' => $hold->currency,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erreur lors de la libération automatique du hold', [
                    'hold_id' => $hold->id,
                    'wallet_id' => $wallet->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($releasedCount > 0) {
            Log::info('Libération automatique de fonds complétée', [
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'holds_released' => $releasedCount,
                'total_amount' => $releasedAmount,
            ]);
        }

        return $releasedCount;
    }

    /**
     * Libérer les fonds expirés pour tous les wallets actifs
     * (Utilisé lors de certaines actions globales si nécessaire)
     */
    public function releaseExpiredHoldsForAllWallets(): array
    {
        // Vérifier si la libération automatique est activée
        if (!\App\Models\Setting::get('wallet_auto_release_enabled', true)) {
            return ['released_count' => 0, 'wallets_affected' => 0];
        }

        $totalReleased = 0;
        $walletsAffected = 0;

        // Récupérer tous les wallets qui ont des holds expirés
        $walletIds = WalletHold::where('status', 'held')
            ->where('held_until', '<=', now())
            ->distinct()
            ->pluck('wallet_id');

        foreach ($walletIds as $walletId) {
            $wallet = Wallet::find($walletId);
            if ($wallet) {
                $released = $this->releaseExpiredHoldsForWallet($wallet);
                if ($released > 0) {
                    $totalReleased += $released;
                    $walletsAffected++;
                }
            }
        }

        return [
            'released_count' => $totalReleased,
            'wallets_affected' => $walletsAffected,
        ];
    }

    /**
     * Vérifier si un wallet a des fonds prêts à être libérés
     */
    public function hasReleasableHolds(Wallet $wallet): bool
    {
        return $wallet->holds()
            ->where('status', 'held')
            ->where('held_until', '<=', now())
            ->exists();
    }

    /**
     * Obtenir le nombre de fonds en attente de libération
     */
    public function getReleasableHoldsCount(Wallet $wallet): int
    {
        return $wallet->holds()
            ->where('status', 'held')
            ->where('held_until', '<=', now())
            ->count();
    }

    /**
     * Obtenir le montant total des fonds en attente de libération
     */
    public function getReleasableHoldsAmount(Wallet $wallet): float
    {
        return $wallet->holds()
            ->where('status', 'held')
            ->where('held_until', '<=', now())
            ->sum('amount');
    }
}

