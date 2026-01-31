<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_events', function (Blueprint $table) {
            $table->id();

            // Ex: PageView, Lead, Purchase, CompleteRegistration...
            $table->string('event_name')->index();
            $table->boolean('is_standard')->default(true);
            $table->boolean('is_active')->default(true)->index();

            // Payload par défaut (optionnel). Ex: { "currency": "USD" }
            $table->json('default_payload')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_events');
    }
};

