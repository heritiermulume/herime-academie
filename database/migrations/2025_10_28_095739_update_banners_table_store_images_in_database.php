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
        Schema::table('banners', function (Blueprint $table) {
            // Changer les colonnes pour stocker les images en base64
            $table->longText('image')->change();
            $table->longText('mobile_image')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // Revenir aux colonnes string
            $table->string('image')->change();
            $table->string('mobile_image')->nullable()->change();
        });
    }
};
