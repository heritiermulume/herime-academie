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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('ambassador_id')->nullable()->after('affiliate_id')->constrained()->onDelete('set null');
            $table->foreignId('ambassador_promo_code_id')->nullable()->after('ambassador_id')->constrained('ambassador_promo_codes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['ambassador_id']);
            $table->dropForeign(['ambassador_promo_code_id']);
            $table->dropColumn(['ambassador_id', 'ambassador_promo_code_id']);
        });
    }
};
