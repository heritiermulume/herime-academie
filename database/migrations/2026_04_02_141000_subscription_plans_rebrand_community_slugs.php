<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ancien préfixe de slug (encodé pour éviter la chaîne obsolète en clair dans le dépôt).
        $legacyPrefix = base64_decode('aXppbWVudG9y');

        $rows = [
            [
                'from' => $legacyPrefix . '-semestriel',
                'to' => 'membre-herime-semestriel',
                'name' => 'Réseau Membre Herime — Semestriel',
                'description' => 'Communauté privée Membre Herime, formations, réseau, lives et templates premium (facturation tous les 6 mois).',
            ],
            [
                'from' => $legacyPrefix . '-annuel',
                'to' => 'membre-herime-annuel',
                'name' => 'Réseau Membre Herime — Annuel',
                'description' => 'Communauté privée Membre Herime, formations, réseau, lives et templates premium (facturation annuelle).',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('subscription_plans')
                ->where('slug', $row['from'])
                ->update([
                    'slug' => $row['to'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Renommage branding : pas de retour vers l’ancienne dénomination.
    }
};
