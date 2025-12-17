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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('currency', 3)->default('USD'); // Devise du wallet
            $table->decimal('balance', 15, 2)->default(0.00); // Solde disponible
            $table->decimal('pending_balance', 15, 2)->default(0.00); // Solde en attente
            $table->decimal('total_earned', 15, 2)->default(0.00); // Total gagné (historique)
            $table->decimal('total_withdrawn', 15, 2)->default(0.00); // Total retiré
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('currency');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
