<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletHold extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'wallet_transaction_id',
        'amount',
        'currency',
        'reason',
        'description',
        'held_at',
        'held_until',
        'released_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'held_at' => 'datetime',
        'held_until' => 'datetime',
        'released_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relation : Le hold appartient à un wallet
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Relation : Le hold peut être lié à une transaction
     */
    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    /**
     * Scope : Holds actifs (en cours de blocage)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'held');
    }

    /**
     * Scope : Holds libérables (date de libération atteinte)
     */
    public function scopeReleasable($query)
    {
        return $query->where('status', 'held')
            ->where('held_until', '<=', now());
    }

    /**
     * Scope : Holds libérés
     */
    public function scopeReleased($query)
    {
        return $query->where('status', 'released');
    }

    /**
     * Vérifier si le hold est libérable
     */
    public function isReleasable(): bool
    {
        return $this->status === 'held' && $this->held_until <= now();
    }

    /**
     * Libérer le hold et rendre l'argent disponible
     */
    public function release(): bool
    {
        if ($this->status !== 'held') {
            return false;
        }

        \DB::beginTransaction();
        try {
            $wallet = $this->wallet;

            // Transférer de held_balance vers available_balance
            $wallet->held_balance -= $this->amount;
            $wallet->available_balance += $this->amount;
            $wallet->save();

            // Marquer le hold comme libéré
            $this->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            // Créer une transaction pour tracer la libération
            $wallet->transactions()->create([
                'type' => 'release',
                'amount' => $this->amount,
                'currency' => $this->currency,
                'balance_before' => $wallet->available_balance - $this->amount,
                'balance_after' => $wallet->available_balance,
                'status' => 'completed',
                'description' => 'Libération de fonds bloqués : ' . $this->description,
                'reference' => 'REL' . time() . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)),
                'transactionable_type' => WalletHold::class,
                'transactionable_id' => $this->id,
                'metadata' => [
                    'hold_id' => $this->id,
                    'reason' => $this->reason,
                ],
            ]);

            \DB::commit();
            
            \Log::info('Hold libéré avec succès', [
                'hold_id' => $this->id,
                'wallet_id' => $wallet->id,
                'amount' => $this->amount,
            ]);

            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur lors de la libération du hold', [
                'hold_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Annuler le hold
     */
    public function cancel(string $reason = null): bool
    {
        if ($this->status !== 'held') {
            return false;
        }

        \DB::beginTransaction();
        try {
            $wallet = $this->wallet;

            // Retirer de held_balance et de balance total
            $wallet->held_balance -= $this->amount;
            $wallet->balance -= $this->amount;
            $wallet->save();

            // Marquer le hold comme annulé
            $this->update([
                'status' => 'cancelled',
                'metadata' => array_merge($this->metadata ?? [], [
                    'cancelled_reason' => $reason,
                    'cancelled_at' => now()->toIso8601String(),
                ]),
            ]);

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur lors de l\'annulation du hold', [
                'hold_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Obtenir le temps restant avant libération
     */
    public function getRemainingTimeAttribute(): ?\DateInterval
    {
        if ($this->status !== 'held') {
            return null;
        }

        $now = now();
        if ($this->held_until <= $now) {
            return null;
        }

        return $now->diff($this->held_until);
    }

    /**
     * Obtenir le temps restant formaté
     */
    public function getFormattedRemainingTimeAttribute(): string
    {
        $interval = $this->remaining_time;

        if (!$interval) {
            return 'Libérable maintenant';
        }

        if ($interval->days > 0) {
            return $interval->days . ' jour' . ($interval->days > 1 ? 's' : '');
        }

        if ($interval->h > 0) {
            return $interval->h . ' heure' . ($interval->h > 1 ? 's' : '');
        }

        return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
    }

    /**
     * Obtenir le label du statut
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'held' => 'En blocage',
            'released' => 'Libéré',
            'cancelled' => 'Annulé',
            default => 'Inconnu',
        };
    }

    /**
     * Obtenir le badge de statut
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'held' => '<span class="badge bg-warning text-dark"><i class="fas fa-lock me-1"></i>En blocage</span>',
            'released' => '<span class="badge bg-success"><i class="fas fa-unlock me-1"></i>Libéré</span>',
            'cancelled' => '<span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Annulé</span>',
            default => '<span class="badge bg-secondary">Inconnu</span>',
        };
    }
}
