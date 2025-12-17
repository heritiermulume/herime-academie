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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->string('type'); // credit, debit, commission, payout, refund, bonus
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->decimal('balance_before', 15, 2); // Solde avant la transaction
            $table->decimal('balance_after', 15, 2); // Solde après la transaction
            $table->string('status')->default('completed'); // pending, completed, failed, cancelled
            $table->string('description')->nullable();
            $table->string('reference')->unique()->nullable(); // Référence unique pour la transaction
            
            // Relations polymorphes pour lier à n'importe quel modèle (Order, Payout, etc.)
            $table->morphs('transactionable', 'wt_transactionable_idx');
            
            // Métadonnées JSON pour des informations supplémentaires
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('wallet_id');
            $table->index('type');
            $table->index('status');
            $table->index('reference');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
