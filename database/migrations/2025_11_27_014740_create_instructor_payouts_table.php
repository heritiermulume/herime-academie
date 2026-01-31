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
        // After the "instructor -> provider" rename, the canonical table is provider_payouts.
        // Make this migration compatible with environments where rename already ran.
        if (Schema::hasTable('provider_payouts') || Schema::hasTable('instructor_payouts')) {
            return;
        }

        Schema::create('provider_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->string('payout_id')->unique(); // ID unique pour Moneroo
            $table->decimal('amount', 10, 2); // Montant à payer au formateur
            $table->decimal('commission_percentage', 5, 2); // Pourcentage de commission retenu
            $table->decimal('commission_amount', 10, 2); // Montant de la commission
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('pawapay_status')->nullable(); // Status retourné par PawaPay (ancien, pour compatibilité)
            $table->string('moneroo_status')->nullable(); // Status retourné par Moneroo
            $table->string('provider_transaction_id')->nullable(); // ID de transaction du fournisseur
            $table->text('failure_reason')->nullable();
            $table->json('pawapay_response')->nullable(); // Réponse complète de PawaPay (ancien, pour compatibilité)
            $table->json('moneroo_response')->nullable(); // Réponse complète de Moneroo
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['provider_id', 'status']);
            $table->index(['order_id']);
            $table->index(['payout_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_payouts');
        Schema::dropIfExists('instructor_payouts');
    }
};
