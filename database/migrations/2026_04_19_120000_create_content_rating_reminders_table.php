<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_rating_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->timestamp('campaign_started_at');
            $table->unsignedSmallInteger('reminders_sent')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'content_id']);
            $table->index(['campaign_started_at', 'reminders_sent'], 'crr_campaign_sent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_rating_reminders');
    }
};
