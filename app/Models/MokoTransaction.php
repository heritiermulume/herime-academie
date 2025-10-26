<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MokoTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'reference',
        'status',
        'trans_status',
        'amount',
        'currency',
        'method',
        'action',
        'customer_number',
        'firstname',
        'lastname',
        'email',
        'user_id',
        'order_id',
        'moko_response',
        'callback_data',
        'comment',
        'error_message',
        'callback_url',
        'moko_created_at',
        'moko_updated_at',
        'callback_received_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'moko_response' => 'array',
        'callback_data' => 'array',
        'moko_created_at' => 'datetime',
        'moko_updated_at' => 'datetime',
        'callback_received_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec la commande
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope pour les transactions en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les transactions réussies
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope pour les transactions échouées
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope par méthode de paiement
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Vérifier si la transaction est réussie
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success' && $this->trans_status === 'Successful';
    }

    /**
     * Vérifier si la transaction est en attente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Vérifier si la transaction a échoué
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Obtenir le nom de la méthode de paiement
     */
    public function getPaymentMethodNameAttribute(): string
    {
        $methods = config('moko.payment_methods');
        return $methods[$this->method]['name'] ?? $this->method;
    }

    /**
     * Obtenir la couleur de la méthode de paiement
     */
    public function getPaymentMethodColorAttribute(): string
    {
        $methods = config('moko.payment_methods');
        return $methods[$this->method]['color'] ?? '#000000';
    }

    /**
     * Obtenir l'icône de la méthode de paiement
     */
    public function getPaymentMethodIconAttribute(): string
    {
        $methods = config('moko.payment_methods');
        return $methods[$this->method]['icon'] ?? 'fas fa-mobile-alt';
    }

    /**
     * Mettre à jour le statut de la transaction
     */
    public function updateStatus(string $status, ?string $transStatus = null, ?string $comment = null): void
    {
        $this->update([
            'status' => $status,
            'trans_status' => $transStatus,
            'comment' => $comment,
            'callback_received_at' => now(),
        ]);
    }

    /**
     * Enregistrer les données du callback
     */
    public function saveCallbackData(array $data): void
    {
        $this->update([
            'callback_data' => $data,
            'callback_received_at' => now(),
        ]);
    }
}
