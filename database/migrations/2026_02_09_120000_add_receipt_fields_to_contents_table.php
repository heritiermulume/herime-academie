<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Champs pour l'envoi optionnel d'un reçu PDF par email à l'inscription.
     */
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->boolean('send_receipt_enabled')->default(false)->after('whatsapp_number');
            $table->string('receipt_custom_title')->nullable()->after('send_receipt_enabled');
            $table->longText('receipt_custom_body')->nullable()->after('receipt_custom_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['send_receipt_enabled', 'receipt_custom_title', 'receipt_custom_body']);
        });
    }
};
