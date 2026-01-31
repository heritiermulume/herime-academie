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
        // Idempotent guard: some environments may already have this table
        // (e.g. created manually or via an older migration history).
        if (Schema::hasTable('course_downloads')) {
            return;
        }

        Schema::create('course_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country', 2)->nullable(); // Code ISO pays (ex: FR, US)
            $table->string('country_name')->nullable(); // Nom du pays
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->enum('download_type', ['file', 'zip'])->default('zip'); // Type de téléchargement
            $table->timestamps();
            
            // Index pour les requêtes fréquentes
            $table->index(['content_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['country', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_downloads');
    }
};
