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
        Schema::create('instructor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('payout_id')->unique(); // UUIDv4 pour pawaPay
            $table->decimal('amount', 10, 2); // Montant à payer au formateur
            $table->decimal('commission_percentage', 5, 2); // Pourcentage de commission retenu
            $table->decimal('commission_amount', 10, 2); // Montant de la commission
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('pawapay_status')->nullable(); // Status retourné par pawaPay
            $table->string('provider_transaction_id')->nullable(); // ID de transaction pawaPay
            $table->text('failure_reason')->nullable();
            $table->json('pawapay_response')->nullable(); // Réponse complète de pawaPay
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['instructor_id', 'status']);
            $table->index(['order_id']);
            $table->index(['payout_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_payouts');
    }
};
