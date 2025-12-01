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
        Schema::create('sent_whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('recipient_phone'); // Numéro de téléphone du destinataire
            $table->string('recipient_name')->nullable();
            $table->string('message_id')->nullable(); // ID du message retourné par l'API
            $table->text('message'); // Contenu du message
            $table->json('attachments')->nullable(); // Pour les médias (images, documents, etc.)
            $table->enum('type', ['announcement', 'custom', 'notification'])->default('custom');
            $table->enum('status', ['sent', 'failed', 'pending', 'delivered', 'read'])->default('sent');
            $table->string('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable(); // Pour stocker des infos supplémentaires
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('recipient_phone');
            $table->index('type');
            $table->index('status');
            $table->index('sent_at');
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_whatsapp_messages');
    }
};
