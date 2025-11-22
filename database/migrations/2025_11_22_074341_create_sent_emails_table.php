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
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->longText('content');
            $table->json('attachments')->nullable();
            $table->enum('type', ['invoice', 'enrollment', 'announcement', 'custom', 'payment'])->default('custom');
            $table->enum('status', ['sent', 'failed', 'pending'])->default('sent');
            $table->string('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable(); // Pour stocker des infos supplémentaires (order_id, course_id, etc.)
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('recipient_email');
            $table->index('type');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_emails');
    }
};
