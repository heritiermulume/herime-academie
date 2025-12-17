<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency',
        'balance',
        'available_balance',
        'held_balance',
        'reserved_balance',
        'total_earned',
        'total_withdrawn',
        'is_active',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'held_balance' => 'decimal:2',
        'reserved_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
    ];

    /**
     * Relation : Le wallet appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation : Le wallet a plusieurs transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Relation : Le wallet a plusieurs payouts
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(WalletPayout::class);
    }

    /**
     * Relation : Le wallet a plusieurs holds (périodes de blocage)
     */
    public function holds(): HasMany
    {
        return $this->hasMany(WalletHold::class);
    }

    /**
     * Relation : Holds actifs
     */
    public function activeHolds(): HasMany
    {
        return $this->hasMany(WalletHold::class)->where('status', 'held');
    }

    /**
     * Créditer le wallet (directement disponible)
     */
    public function credit(float $amount, string $type, string $description = null, $transactionable = null, array $metadata = []): WalletTransaction
    {
        \DB::beginTransaction();
        try {
            $balanceBefore = $this->balance;
            $this->balance += $amount;
            $this->available_balance += $amount;
            $this->total_earned += $amount;
            $this->last_transaction_at = now();
            $this->save();

            $transaction = $this->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'currency' => $this->currency,
                'balance_before' => $balanceBefore,
                'balance_after' => $this->balance,
                'status' => 'completed',
                'description' => $description,
                'reference' => $this->generateReference(),
                'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                'transactionable_id' => $transactionable ? $transactionable->id : null,
                'metadata' => $metadata,
            ]);

            \DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Créditer le wallet avec période de blocage (holding period)
     */
    public function creditWithHold(
        float $amount, 
        string $type, 
        int $holdingDays = null, 
        string $description = null, 
        $transactionable = null, 
        array $metadata = []
    ): array {
        \DB::beginTransaction();
        try {
            // Utiliser le délai configuré ou le délai par défaut (7 jours)
            if ($holdingDays === null) {
                $holdingDays = (int) config('wallet.holding_period_days', 7);
            }

            $balanceBefore = $this->balance;
            
            // Augmenter le solde total et le solde bloqué
            $this->balance += $amount;
            $this->held_balance += $amount;
            $this->total_earned += $amount;
            $this->last_transaction_at = now();
            $this->save();

            // Créer la transaction
            $transaction = $this->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'currency' => $this->currency,
                'balance_before' => $balanceBefore,
                'balance_after' => $this->balance,
                'status' => 'completed',
                'description' => $description . ' (Disponible dans ' . $holdingDays . ' jours)',
                'reference' => $this->generateReference(),
                'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                'transactionable_id' => $transactionable ? $transactionable->id : null,
                'metadata' => array_merge($metadata, [
                    'holding_period_days' => $holdingDays,
                    'held' => true,
                ]),
            ]);

            // Créer le hold
            $hold = $this->holds()->create([
                'wallet_transaction_id' => $transaction->id,
                'amount' => $amount,
                'currency' => $this->currency,
                'reason' => $type,
                'description' => $description,
                'held_at' => now(),
                'held_until' => now()->addDays($holdingDays),
                'status' => 'held',
                'metadata' => $metadata,
            ]);

            \DB::commit();
            
            \Log::info('Crédit avec hold créé', [
                'wallet_id' => $this->id,
                'amount' => $amount,
                'holding_days' => $holdingDays,
                'held_until' => $hold->held_until,
            ]);

            return [
                'transaction' => $transaction,
                'hold' => $hold,
            ];
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Débiter le wallet
     */
    public function debit(float $amount, string $type, string $description = null, $transactionable = null, array $metadata = []): WalletTransaction
    {
        \DB::beginTransaction();
        try {
            // Vérifier le solde DISPONIBLE (pas le solde total)
            if ($this->available_balance < $amount) {
                throw new \Exception("Solde disponible insuffisant. Vous avez {$this->available_balance} {$this->currency} disponibles, mais vous essayez de retirer {$amount} {$this->currency}.");
            }

            $balanceBefore = $this->balance;
            $this->balance -= $amount;
            $this->available_balance -= $amount;
            $this->total_withdrawn += $amount;
            $this->last_transaction_at = now();
            $this->save();

            $transaction = $this->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'currency' => $this->currency,
                'balance_before' => $balanceBefore,
                'balance_after' => $this->balance,
                'status' => 'completed',
                'description' => $description,
                'reference' => $this->generateReference(),
                'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                'transactionable_id' => $transactionable ? $transactionable->id : null,
                'metadata' => $metadata,
            ]);

            \DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Générer une référence unique pour la transaction
     */
    private function generateReference(): string
    {
        return 'WTX' . time() . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }

    /**
     * Vérifier si le wallet a suffisamment de solde DISPONIBLE
     */
    public function hasBalance(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }

    /**
     * Libérer tous les holds éligibles
     */
    public function releaseExpiredHolds(): int
    {
        $releasedCount = 0;
        $holds = $this->holds()->releasable()->get();

        foreach ($holds as $hold) {
            if ($hold->release()) {
                $releasedCount++;
            }
        }

        return $releasedCount;
    }

    /**
     * Obtenir le solde formaté
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2, '.', ',') . ' ' . $this->currency;
    }

    /**
     * Obtenir les transactions récentes
     */
    public function recentTransactions(int $limit = 10)
    {
        return $this->transactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les payouts en attente
     */
    public function pendingPayouts()
    {
        return $this->payouts()
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtenir les statistiques du wallet
     */
    public function getStats(): array
    {
        return [
            'balance' => $this->balance,
            'pending_balance' => $this->pending_balance,
            'total_earned' => $this->total_earned,
            'total_withdrawn' => $this->total_withdrawn,
            'total_transactions' => $this->transactions()->count(),
            'completed_payouts' => $this->payouts()->where('status', 'completed')->count(),
            'pending_payouts' => $this->payouts()->whereIn('status', ['pending', 'processing'])->count(),
        ];
    }
}
