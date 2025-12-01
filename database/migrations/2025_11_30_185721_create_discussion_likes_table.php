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
        Schema::create('discussion_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('discussion_id')->constrained('lesson_discussions')->onDelete('cascade');
            $table->timestamps();
            
            // Contrainte unique pour empêcher un utilisateur de liker plusieurs fois la même discussion
            $table->unique(['user_id', 'discussion_id']);
            $table->index('discussion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_likes');
    }
};
