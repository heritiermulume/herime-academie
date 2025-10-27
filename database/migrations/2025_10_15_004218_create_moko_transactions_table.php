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
        Schema::create('moko_transactions', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('transaction_id')->unique(); // ID de transaction MOKO
            $table->string('reference')->unique(); // Référence unique de la transaction
            $table->string('status')->default('pending'); // pending, success, failed, cancelled
            $table->string('trans_status')->nullable(); // Statut final de MOKO
            
            // Informations de paiement
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('CDF');
            $table->string('method'); // airtel, orange, mpesa, africell
            $table->string('action'); // debit, credit
            
            // Informations client
            $table->string('customer_number');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            
            // Relations
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            
            // Réponses MOKO
            $table->json('moko_response')->nullable(); // Réponse initiale de MOKO
            $table->json('callback_data')->nullable(); // Données du callback
            $table->text('comment')->nullable(); // Commentaire de MOKO
            $table->text('error_message')->nullable(); // Message d'erreur si échec
            
            // Métadonnées
            $table->string('callback_url')->nullable();
            $table->timestamp('moko_created_at')->nullable();
            $table->timestamp('moko_updated_at')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['user_id', 'status']);
            $table->index(['reference']);
            $table->index(['transaction_id']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moko_transactions');
    }
};
