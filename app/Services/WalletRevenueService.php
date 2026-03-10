<?php

namespace App\Services;

use App\Models\AmbassadorCommission;
use App\Models\Order;
use App\Models\ProviderPayout;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Alimente les wallets plateforme et ambassadeurs à partir des revenus (commandes, commissions).
 * Idempotent : ne crédite qu'une seule fois par commande / payout / commission.
 */
class WalletRevenueService
{
    private ?User $platformUser = null;

    /**
     * Utilisateur "plateforme" auquel sont rattachés les wallets de la plateforme (revenus internes + commissions prestataires).
     */
    public function getPlatformUser(): User
    {
        if ($this->platformUser !== null) {
            return $this->platformUser;
        }

        $email = config('wallet.platform_email', 'platform@herime-academie.com');
        $this->platformUser = User::where('email', $email)->first();

        if (!$this->platformUser) {
            $this->platformUser = User::create([
                'name' => 'Plateforme',
                'email' => $email,
                'password' => bcrypt(Str::random(32)),
                'role' => 'admin',
                'is_active' => true,
            ]);
            Log::info('WalletRevenueService: utilisateur plateforme créé', ['user_id' => $this->platformUser->id]);
        }

        return $this->platformUser;
    }

    /**
     * Wallet plateforme pour une devise (créé si nécessaire).
     */
    public function getOrCreatePlatformWallet(string $currency): Wallet
    {
        $user = $this->getPlatformUser();
        $currency = strtoupper($currency);

        return Wallet::firstOrCreate(
            [
                'user_id' => $user->id,
                'currency' => $currency,
            ],
            [
                'balance' => 0,
                'available_balance' => 0,
                'held_balance' => 0,
                'reserved_balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'is_active' => true,
            ]
        );
    }

    /**
     * Wallet ambassadeur pour un user_id et une devise (créé si nécessaire).
     */
    public function getOrCreateUserWallet(int $userId, string $currency): Wallet
    {
        $currency = strtoupper($currency);

        return Wallet::firstOrCreate(
            [
                'user_id' => $userId,
                'currency' => $currency,
            ],
            [
                'balance' => 0,
                'available_balance' => 0,
                'held_balance' => 0,
                'reserved_balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'is_active' => true,
            ]
        );
    }

    /**
     * Commande "interne" = sans contenu prestataire (revenu 100 % plateforme).
     */
    private function isInternalOrder(Order $order): bool
    {
        $order->load('orderItems.content.provider');
        foreach ($order->orderItems as $item) {
            $provider = $item->content?->provider;
            if ($provider && $provider->role === 'provider') {
                return false;
            }
        }
        return true;
    }

    /**
     * Créditer le wallet plateforme à partir d'une commande payée (revenu interne uniquement). Idempotent.
     * Si la commande a une commission ambassadeur, on crédite la plateforme avec (total - commission).
     */
    public function creditPlatformFromOrder(Order $order): bool
    {
        if (!in_array($order->status, ['paid', 'completed'])) {
            return false;
        }
        if (!$this->isInternalOrder($order)) {
            return false;
        }

        $amount = (float) ($order->total_amount ?? $order->total ?? 0);
        $commission = AmbassadorCommission::where('order_id', $order->id)->first();
        if ($commission) {
            $amount -= (float) $commission->commission_amount;
        }
        if ($amount <= 0) {
            return false;
        }

        $exists = WalletTransaction::where('transactionable_type', Order::class)
            ->where('transactionable_id', $order->id)
            ->whereIn('type', ['credit', 'commission'])
            ->exists();
        if ($exists) {
            return false;
        }

        try {
            $currency = strtoupper($order->currency ?? 'USD');
            $wallet = $this->getOrCreatePlatformWallet($currency);
            $wallet->credit(
                $amount,
                'credit',
                'Revenu commande #' . ($order->order_number ?? $order->id),
                $order,
                ['order_id' => $order->id]
            );
            Log::info('WalletRevenueService: plateforme créditée depuis commande', [
                'order_id' => $order->id,
                'amount' => $amount,
                'currency' => $currency,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('WalletRevenueService: erreur crédit plateforme depuis commande', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Créditer le wallet plateforme à partir d'une commission ProviderPayout. Idempotent.
     */
    public function creditPlatformFromProviderPayout(ProviderPayout $payout): bool
    {
        if ($payout->status !== 'completed') {
            return false;
        }

        $amount = (float) $payout->commission_amount;
        if ($amount <= 0) {
            return false;
        }

        $exists = WalletTransaction::where('transactionable_type', ProviderPayout::class)
            ->where('transactionable_id', $payout->id)
            ->whereIn('type', ['credit', 'commission'])
            ->exists();
        if ($exists) {
            return false;
        }

        try {
            $currency = strtoupper($payout->currency ?? 'USD');
            $wallet = $this->getOrCreatePlatformWallet($currency);
            $wallet->credit(
                $amount,
                'commission',
                'Commission prestataire payout #' . $payout->id,
                $payout,
                ['provider_payout_id' => $payout->id]
            );
            Log::info('WalletRevenueService: plateforme créditée depuis ProviderPayout', [
                'provider_payout_id' => $payout->id,
                'amount' => $amount,
                'currency' => $currency,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('WalletRevenueService: erreur crédit plateforme depuis ProviderPayout', [
                'provider_payout_id' => $payout->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Créditer le wallet ambassadeur à partir d'une commission. Idempotent.
     */
    public function creditAmbassadorFromCommission(Order $order, AmbassadorCommission $commission): bool
    {
        $amount = (float) $commission->commission_amount;
        if ($amount <= 0) {
            return false;
        }

        $exists = WalletTransaction::where('transactionable_type', AmbassadorCommission::class)
            ->where('transactionable_id', $commission->id)
            ->whereIn('type', ['credit', 'commission'])
            ->exists();
        if ($exists) {
            return false;
        }

        $ambassador = $commission->ambassador;
        if (!$ambassador || !$ambassador->user_id) {
            return false;
        }

        try {
            $currency = strtoupper($order->currency ?? 'USD');
            $wallet = $this->getOrCreateUserWallet($ambassador->user_id, $currency);
            $useHold = (int) \App\Models\Setting::get('wallet_holding_period_days', 0) > 0;
            if ($useHold) {
                $wallet->creditWithHold(
                    $amount,
                    'commission',
                    null,
                    'Commission commande #' . ($order->order_number ?? $order->id),
                    $commission,
                    ['ambassador_commission_id' => $commission->id]
                );
            } else {
                $wallet->credit(
                    $amount,
                    'commission',
                    'Commission commande #' . ($order->order_number ?? $order->id),
                    $commission,
                    ['ambassador_commission_id' => $commission->id]
                );
            }
            Log::info('WalletRevenueService: ambassadeur crédité depuis commission', [
                'commission_id' => $commission->id,
                'ambassador_id' => $ambassador->id,
                'amount' => $amount,
                'currency' => $currency,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('WalletRevenueService: erreur crédit ambassadeur depuis commission', [
                'commission_id' => $commission->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
