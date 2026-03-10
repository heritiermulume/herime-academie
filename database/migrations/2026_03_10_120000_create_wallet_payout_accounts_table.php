<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Comptes de paiement (bénéficiaires) qui recevront les payouts - configuration admin.
     */
    public function up(): void
    {
        Schema::create('wallet_payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Libellé du compte (ex: "Compte principal M-Pesa")
            $table->string('country_code', 2); // Code pays (provenant du fournisseur)
            $table->string('method', 64); // Code opérateur (ex: mtn_momo, orange_money)
            $table->string('phone', 20); // Numéro pour recevoir le payout
            $table->string('currency', 3)->default('USD');
            $table->string('recipient_first_name')->nullable();
            $table->string('recipient_last_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['country_code', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_payout_accounts');
    }
};
