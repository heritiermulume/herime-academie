<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Active l'envoi du reçu PDF par défaut pour tous les contenus existants,
     * afin que chaque inscription reçoive un reçu (téléchargeable ou pas, gratuit ou payant).
     */
    public function up(): void
    {
        if (Schema::hasTable('contents') && Schema::hasColumn('contents', 'send_receipt_enabled')) {
            DB::table('contents')->update(['send_receipt_enabled' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionnel : remettre à false si besoin
        if (Schema::hasTable('contents') && Schema::hasColumn('contents', 'send_receipt_enabled')) {
            DB::table('contents')->update(['send_receipt_enabled' => false]);
        }
    }
};
