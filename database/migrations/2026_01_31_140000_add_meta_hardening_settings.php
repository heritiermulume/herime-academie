<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Defaults "safe-prod":
        // - GeoIP fallback OFF (évite dépendance externe; activer si besoin)
        // - Consent OFF (à activer si votre site nécessite un banner RGPD)
        // - CAPI OFF (nécessite un token)
        DB::table('settings')->updateOrInsert(
            ['key' => 'meta_geoip_fallback_enabled'],
            [
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Activer le fallback GeoIP (service externe) pour détecter le pays',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'meta_consent_required'],
            [
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Exiger un consentement avant de charger Meta Pixel',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'meta_consent_cookie_name'],
            [
                'value' => 'meta_consent',
                'type' => 'string',
                'description' => 'Nom du cookie (valeur "1") qui indique le consentement Meta',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'meta_capi_enabled'],
            [
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Activer Meta Conversions API (CAPI) pour déduplication et fiabilité',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'meta_capi_access_token'],
            [
                'value' => '',
                'type' => 'string',
                'description' => 'CAPI Access Token (Graph API) — à garder privé',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'meta_capi_test_event_code'],
            [
                'value' => '',
                'type' => 'string',
                'description' => 'CAPI Test Event Code (optionnel, Events Manager)',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'meta_geoip_fallback_enabled',
            'meta_consent_required',
            'meta_consent_cookie_name',
            'meta_capi_enabled',
            'meta_capi_access_token',
            'meta_capi_test_event_code',
        ])->delete();
    }
};

