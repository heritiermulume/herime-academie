<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'wallet_transaction_id',
        'amount',
        'currency',
        'status',
        'moneroo_id',
        'method',
        'phone',
        'country',
        'description',
        'customer_email',
        'customer_first_name',
        'customer_last_name',
        'fee',
        'net_amount',
        'moneroo_data',
        'failure_reason',
        'initiated_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'moneroo_data' => 'array',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Relation : Le payout appartient à un wallet
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Relation : Le payout peut être lié à une transaction wallet
     */
    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    /**
     * Scope : Payouts en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope : Payouts en cours de traitement
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope : Payouts complétés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope : Payouts échoués
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Obtenir le montant formaté
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, '.', ',') . ' ' . $this->currency;
    }

    /**
     * Obtenir le badge de statut
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>En attente</span>',
            'processing' => '<span class="badge bg-info"><i class="fas fa-spinner fa-spin me-1"></i>En cours</span>',
            'completed' => '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Complété</span>',
            'failed' => '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Échoué</span>',
            'cancelled' => '<span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Annulé</span>',
            default => '<span class="badge bg-secondary">Inconnu</span>',
        };
    }

    /**
     * Obtenir le nom du provider
     */
    public function getProviderNameAttribute(): string
    {
        return match($this->method) {
            'mtn_cd' => 'MTN Mobile Money (RDC)',
            'airtel_cd' => 'Airtel Money (RDC)',
            'orange_cd' => 'Orange Money (RDC)',
            'mtn_bj' => 'MTN Mobile Money (Bénin)',
            'moov_bj' => 'Moov Money (Bénin)',
            'mtn_ci' => 'MTN Mobile Money (Côte d\'Ivoire)',
            'orange_ci' => 'Orange Money (Côte d\'Ivoire)',
            'wave_sn' => 'Wave (Sénégal)',
            'orange_sn' => 'Orange Money (Sénégal)',
            default => strtoupper($this->method),
        };
    }

    /**
     * Marquer le payout comme en cours
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => 'processing',
        ]);
    }

    /**
     * Marquer le payout comme complété
     */
    public function markAsCompleted(array $data = []): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'moneroo_data' => array_merge($this->moneroo_data ?? [], $data),
        ]);
    }

    /**
     * Marquer le payout comme échoué
     */
    public function markAsFailed(string $reason = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Vérifier si le payout peut être annulé
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending']);
    }

    /**
     * Annuler le payout
     */
    public function cancel(string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        \DB::beginTransaction();
        try {
            // Rembourser le montant dans le wallet
            $wallet = $this->wallet;
            $wallet->balance += $this->amount;
            $wallet->total_withdrawn -= $this->amount;
            $wallet->save();

            // Créer une transaction de remboursement
            $wallet->credit(
                $this->amount,
                'refund',
                'Remboursement du retrait annulé #' . $this->id,
                $this,
                ['payout_id' => $this->id, 'reason' => $reason]
            );

            // Marquer le payout comme annulé
            $this->update([
                'status' => 'cancelled',
                'failure_reason' => $reason,
            ]);

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur lors de l\'annulation du payout', [
                'payout_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
