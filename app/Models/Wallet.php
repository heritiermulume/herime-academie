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
        'pending_balance',
        'total_earned',
        'total_withdrawn',
        'is_active',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
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
     * Créditer le wallet
     */
    public function credit(float $amount, string $type, string $description = null, $transactionable = null, array $metadata = []): WalletTransaction
    {
        \DB::beginTransaction();
        try {
            $balanceBefore = $this->balance;
            $this->balance += $amount;
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
     * Débiter le wallet
     */
    public function debit(float $amount, string $type, string $description = null, $transactionable = null, array $metadata = []): WalletTransaction
    {
        \DB::beginTransaction();
        try {
            if ($this->balance < $amount) {
                throw new \Exception("Solde insuffisant. Vous avez {$this->balance} {$this->currency}, mais vous essayez de retirer {$amount} {$this->currency}.");
            }

            $balanceBefore = $this->balance;
            $this->balance -= $amount;
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
     * Vérifier si le wallet a suffisamment de solde
     */
    public function hasBalance(float $amount): bool
    {
        return $this->balance >= $amount;
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
