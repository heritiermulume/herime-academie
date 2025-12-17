<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Solde disponible au retrait (balance - held_balance)
            $table->decimal('available_balance', 15, 2)->default(0.00)->after('balance');
            
            // Solde en période de blocage (holding period)
            $table->decimal('held_balance', 15, 2)->default(0.00)->after('available_balance');
            
            // Renommer pending_balance en reserved_balance pour plus de clarté
            // (utilisé pour les retraits en cours mais pas encore complétés)
            $table->renameColumn('pending_balance', 'reserved_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['available_balance', 'held_balance']);
            $table->renameColumn('reserved_balance', 'pending_balance');
        });
    }
};
