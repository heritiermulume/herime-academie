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
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('lesson_id');
            $table->boolean('is_completed')->default(false);
            $table->integer('time_watched')->default(0); // en secondes
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['user_id', 'content_id']);
            $table->unique(['user_id', 'lesson_id']); // Un seul enregistrement par utilisateur/leçon
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};