<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletPayoutAccount extends Model
{
    use HasFactory;

    protected $table = 'wallet_payout_accounts';

    protected $fillable = [
        'name',
        'country_code',
        'method',
        'phone',
        'currency',
        'recipient_first_name',
        'recipient_last_name',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Nom complet du bénéficiaire
     */
    public function getRecipientFullNameAttribute(): string
    {
        return trim(($this->recipient_first_name ?? '') . ' ' . ($this->recipient_last_name ?? '')) ?: $this->name;
    }

    /**
     * Scope : comptes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Définir comme compte par défaut (et retirer le défaut des autres)
     */
    public function setAsDefault(): void
    {
        static::where('id', '!=', $this->id)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}
