<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute le soft delete (deleted_at) à toutes les tables liées aux finances
     * pour sécuriser les données de transactions et permettre l'audit.
     */
    public function up(): void
    {
        // Tables principales de commandes et paiements
        $tables = [
            'orders',
            'order_items',
            'payments',
            'wallet_transactions',
            'wallet_payouts',
            'provider_payouts',
            'instructor_payouts', // Ancien nom, pour compatibilité
            'ambassador_commissions',
            'coupons',
            'contents', // Cours/contenus (liés aux transactions)
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'orders',
            'order_items',
            'payments',
            'wallet_transactions',
            'wallet_payouts',
            'provider_payouts',
            'instructor_payouts',
            'ambassador_commissions',
            'coupons',
            'contents',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
