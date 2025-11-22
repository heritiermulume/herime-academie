<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'recipient_type',
        'recipient_config',
        'subject',
        'content',
        'attachments',
        'status',
        'scheduled_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'error_message',
        'started_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'recipient_config' => 'array',
        'attachments' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relation avec le créateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope pour les emails en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope pour les emails en cours de traitement
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope pour les emails complétés
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Vérifier si l'email peut être envoyé
     */
    public function canBeSent(): bool
    {
        return $this->status === 'pending' 
            && $this->scheduled_at <= now();
    }

    /**
     * Marquer comme en cours de traitement
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Marquer comme complété
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
