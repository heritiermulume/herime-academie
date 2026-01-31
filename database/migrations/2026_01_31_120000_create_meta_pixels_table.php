<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_pixels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('pixel_id')->index();
            $table->boolean('is_active')->default(true)->index();

            // Conditions optionnelles (scalabilité: filtrage côté serveur)
            $table->json('allowed_country_codes')->nullable();
            $table->json('excluded_country_codes')->nullable();
            $table->json('funnel_keys')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_pixels');
    }
};

