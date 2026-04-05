<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Préfixe historique des slugs d’adhésion (encodé hex ; les cibles sont membre-herime-*).
        $obsoletePrefix = hex2bin('697a696d656e746f72');

        $rows = [
            [
                'from' => $obsoletePrefix.'-trimestriel',
                'to' => 'membre-herime-trimestriel',
                'name' => 'Réseau Membre Herime — Trimestriel',
                'description' => 'Communauté privée Membre Herime, formations, réseau, lives et templates premium (facturation tous les 3 mois).',
            ],
            [
                'from' => $obsoletePrefix.'-semestriel',
                'to' => 'membre-herime-semestriel',
                'name' => 'Réseau Membre Herime — Semestriel',
                'description' => 'Communauté privée Membre Herime, formations, réseau, lives et templates premium (facturation tous les 6 mois).',
            ],
            [
                'from' => $obsoletePrefix.'-annuel',
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
