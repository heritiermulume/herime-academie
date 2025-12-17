<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'currency',
        'balance_before',
        'balance_after',
        'status',
        'description',
        'reference',
        'transactionable_type',
        'transactionable_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Relation : La transaction appartient à un wallet
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Relation polymorphe : La transaction peut être liée à n'importe quel modèle
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope : Transactions de type crédit
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope : Transactions de type débit
     */
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Scope : Transactions complétées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope : Transactions en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Obtenir le montant formaté
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, '.', ',') . ' ' . $this->currency;
    }

    /**
     * Vérifier si la transaction est un crédit
     */
    public function isCredit(): bool
    {
        return in_array($this->type, ['credit', 'commission', 'bonus', 'refund']);
    }

    /**
     * Vérifier si la transaction est un débit
     */
    public function isDebit(): bool
    {
        return in_array($this->type, ['debit', 'payout', 'withdrawal']);
    }

    /**
     * Obtenir l'icône selon le type de transaction
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'credit' => 'fas fa-plus-circle text-success',
            'debit' => 'fas fa-minus-circle text-danger',
            'commission' => 'fas fa-percent text-primary',
            'payout' => 'fas fa-money-bill-wave text-warning',
            'withdrawal' => 'fas fa-cash-register text-warning',
            'refund' => 'fas fa-undo text-info',
            'bonus' => 'fas fa-gift text-success',
            default => 'fas fa-exchange-alt text-secondary',
        };
    }

    /**
     * Obtenir le label selon le type de transaction
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'credit' => 'Crédit',
            'debit' => 'Débit',
            'commission' => 'Commission',
            'payout' => 'Retrait',
            'withdrawal' => 'Retrait',
            'refund' => 'Remboursement',
            'bonus' => 'Bonus',
            default => 'Transaction',
        };
    }

    /**
     * Obtenir le badge de statut
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'completed' => '<span class="badge bg-success">Complété</span>',
            'pending' => '<span class="badge bg-warning">En attente</span>',
            'failed' => '<span class="badge bg-danger">Échoué</span>',
            'cancelled' => '<span class="badge bg-secondary">Annulé</span>',
            default => '<span class="badge bg-secondary">Inconnu</span>',
        };
    }
}
