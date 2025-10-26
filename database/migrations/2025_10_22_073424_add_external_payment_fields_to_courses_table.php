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
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('use_external_payment')->default(false)->after('is_free');
            $table->string('external_payment_url')->nullable()->after('use_external_payment');
            $table->string('external_payment_text')->nullable()->after('external_payment_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['use_external_payment', 'external_payment_url', 'external_payment_text']);
        });
    }
};
