<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('contents')->cascadeOnDelete();
            $table->foreignId('started_by')->constrained('users')->cascadeOnDelete();
            $table->string('room_name', 80);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index('started_at');
        });

        Schema::create('live_training_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('live_training_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('display_name', 255)->nullable();
            $table->string('jitsi_participant_id', 120)->nullable();
            $table->timestamp('joined_at');
            $table->timestamp('left_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamps();

            $table->index(['session_id', 'user_id']);
            $table->index('joined_at');
        });

        Schema::create('live_training_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('live_training_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_name', 255)->nullable();
            $table->text('message');
            $table->string('message_type', 30)->default('chat');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['session_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_training_messages');
        Schema::dropIfExists('live_training_participants');
        Schema::dropIfExists('live_training_sessions');
    }
};
