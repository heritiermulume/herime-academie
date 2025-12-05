<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ambassador extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_id',
        'total_earnings',
        'pending_earnings',
        'paid_earnings',
        'total_referrals',
        'total_sales',
        'is_active',
        'activated_at',
    ];

    protected $casts = [
        'total_earnings' => 'decimal:2',
        'pending_earnings' => 'decimal:2',
        'paid_earnings' => 'decimal:2',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the application
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(AmbassadorApplication::class, 'application_id');
    }

    /**
     * Get promo codes
     */
    public function promoCodes(): HasMany
    {
        return $this->hasMany(AmbassadorPromoCode::class);
    }

    /**
     * Get active promo code
     */
    public function activePromoCode()
    {
        return $this->promoCodes()->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
            })
            ->first();
    }

    /**
     * Get commissions
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(AmbassadorCommission::class);
    }

    /**
     * Get orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Generate a unique promo code for this ambassador
     */
    public function generatePromoCode(): AmbassadorPromoCode
    {
        // Extraire une partie du nom de l'ambassadeur
        $userName = $this->user->name ?? 'AMB';
        
        // Prendre le premier mot du nom (pour les noms composés)
        $firstName = explode(' ', trim($userName))[0];
        
        // Nettoyer le nom : enlever les accents, espaces et caractères spéciaux
        $cleanName = $this->cleanNameForCode($firstName);
        
        // Prendre les 3-4 premiers caractères du nom (ou tout si moins de 3 caractères)
        $namePrefix = strtoupper(substr($cleanName, 0, min(4, strlen($cleanName))));
        
        // Si le nom est trop court, utiliser "AMB" comme préfixe
        if (strlen($namePrefix) < 3) {
            $namePrefix = 'AMB';
        }
        
        // Générer le code avec le préfixe du nom + partie aléatoire
        $code = strtoupper($namePrefix . '-' . Str::random(6));
        
        // Ensure uniqueness
        while (AmbassadorPromoCode::where('code', $code)->exists()) {
            $code = strtoupper($namePrefix . '-' . Str::random(6));
        }

        return $this->promoCodes()->create([
            'code' => $code,
            'name' => 'Code promo ' . $this->user->name,
            'description' => 'Code promo ambassadeur',
            'is_active' => true,
        ]);
    }

    /**
     * Clean name for promo code generation
     * Remove accents, spaces, and special characters
     */
    private function cleanNameForCode(string $name): string
    {
        // Convertir en minuscules
        $name = mb_strtolower($name, 'UTF-8');
        
        // Remplacer les accents
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ç' => 'c', 'ñ' => 'n',
        ];
        $name = strtr($name, $accents);
        
        // Garder uniquement les lettres et chiffres
        $name = preg_replace('/[^a-z0-9]/', '', $name);
        
        return $name;
    }

    /**
     * Add earnings
     */
    public function addEarnings($amount)
    {
        $this->increment('total_earnings', $amount);
        $this->increment('pending_earnings', $amount);
    }

    /**
     * Mark earnings as paid
     */
    public function markAsPaid($amount)
    {
        $this->decrement('pending_earnings', $amount);
        $this->increment('paid_earnings', $amount);
    }

    /**
     * Increment referrals
     */
    public function incrementReferrals()
    {
        $this->increment('total_referrals');
    }

    /**
     * Increment sales
     */
    public function incrementSales()
    {
        $this->increment('total_sales');
    }

    /**
     * Scope for active ambassadors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
