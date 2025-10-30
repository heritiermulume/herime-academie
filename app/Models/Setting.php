<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Récupérer une valeur de setting
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        return match($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($setting->value) ? (str_contains($setting->value, '.') ? (float) $setting->value : (int) $setting->value) : $default,
            'json' => json_decode($setting->value, true) ?? $default,
            default => $setting->value ?? $default,
        };
    }

    /**
     * Définir une valeur de setting
     */
    public static function set(string $key, $value, string $type = 'string', string $description = null): void
    {
        $setting = self::firstOrNew(['key' => $key]);
        
        $setting->value = match($type) {
            'boolean' => $value ? '1' : '0',
            'number', 'json' => is_array($value) || is_object($value) ? json_encode($value) : (string) $value,
            default => (string) $value,
        };
        
        $setting->type = $type;
        if ($description) {
            $setting->description = $description;
        }
        
        $setting->save();
    }

    /**
     * Récupérer la devise de base du site
     */
    public static function getBaseCurrency(): string
    {
        return self::get('base_currency', 'USD');
    }
}
