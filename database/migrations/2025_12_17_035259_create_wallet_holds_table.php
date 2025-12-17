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
        Schema::create('wallet_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_transaction_id')->nullable()->constrained()->onDelete('set null');
            
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->string('reason'); // commission, bonus, refund, manual
            $table->text('description')->nullable();
            
            // Période de blocage
            $table->timestamp('held_at'); // Date de début du blocage
            $table->timestamp('held_until'); // Date de libération prévue
            $table->timestamp('released_at')->nullable(); // Date réelle de libération
            
            $table->string('status')->default('held'); // held, released, cancelled
            
            // Métadonnées
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index('wallet_id');
            $table->index('status');
            $table->index('held_until');
            $table->index('released_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_holds');
    }
};
