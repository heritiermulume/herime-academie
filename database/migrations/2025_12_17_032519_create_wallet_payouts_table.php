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
        Schema::create('wallet_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->string('status')->default('pending'); // pending, processing, completed, failed, cancelled
            
            // Informations Moneroo
            $table->string('moneroo_id')->unique()->nullable(); // ID du payout chez Moneroo
            $table->string('method'); // mtn_cd, airtel_cd, orange_cd, etc.
            $table->string('phone'); // Numéro de téléphone du bénéficiaire
            $table->string('country', 3); // Code pays (CD, BJ, etc.)
            $table->string('description')->nullable();
            
            // Informations du bénéficiaire
            $table->string('customer_email');
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            
            // Frais et montants
            $table->decimal('fee', 15, 2)->default(0.00); // Frais de transaction
            $table->decimal('net_amount', 15, 2)->nullable(); // Montant net reçu
            
            // Métadonnées Moneroo
            $table->json('moneroo_data')->nullable(); // Réponse complète de Moneroo
            $table->text('failure_reason')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('wallet_id');
            $table->index('status');
            $table->index('moneroo_id');
            $table->index('method');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_payouts');
    }
};
