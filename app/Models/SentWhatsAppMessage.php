<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentWhatsAppMessage extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée au modèle
     */
    protected $table = 'sent_whatsapp_messages';

    protected $fillable = [
        'user_id',
        'recipient_phone',
        'recipient_name',
        'message_id',
        'message',
        'attachments',
        'type',
        'status',
        'error_message',
        'sent_at',
        'metadata',
    ];

    protected $casts = [
        'attachments' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les messages envoyés avec succès
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope pour les messages échoués
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
