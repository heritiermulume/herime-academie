<?php

namespace App\Helpers;

use App\Models\Setting;

class CurrencyHelper
{
    /**
     * Récupérer la devise de base du site
     */
    public static function getBaseCurrency(): string
    {
        return Setting::getBaseCurrency();
    }

    /**
     * Formater un montant avec la devise de base
     */
    public static function format(float|int|string|null $amount, ?string $currency = null): string
    {
        $currency = $currency ?? self::getBaseCurrency();
        $amount = is_numeric($amount) ? (float) $amount : 0.0;
        return number_format($amount, 2, '.', ' ') . ' ' . $currency;
    }

    /**
     * Formater un montant avec symbole de devise
     */
    public static function formatWithSymbol(float|int|string|null $amount, ?string $currency = null): string
    {
        $currency = $currency ?? self::getBaseCurrency();
        $amount = is_numeric($amount) ? (float) $amount : 0.0;
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'CDF' => 'FC',
            'XOF' => 'CFA',
            'XAF' => 'FCFA',
            'RWF' => 'RF',
            'KES' => 'KSh',
            'UGX' => 'USh',
            'TZS' => 'TSh',
            'GHS' => 'GH₵',
            'NGN' => '₦',
            'ZAR' => 'R',
        ];
        
        $symbol = $symbols[$currency] ?? $currency;
        
        // Pour les symboles à gauche
        if (in_array($currency, ['USD', 'EUR', 'GHS', 'NGN', 'ZAR'])) {
            return $symbol . number_format($amount, 2, '.', ' ');
        }
        
        // Pour les symboles à droite
        return number_format($amount, 2, '.', ' ') . ' ' . $symbol;
    }
}

