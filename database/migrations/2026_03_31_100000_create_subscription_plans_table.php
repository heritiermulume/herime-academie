<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('plan_type', ['recurring', 'one_time', 'freemium'])->default('recurring');
            $table->enum('billing_period', ['monthly', 'yearly'])->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('annual_discount_percent', 5, 2)->default(0);
            $table->unsignedInteger('trial_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_renew_default')->default(true);
            $table->foreignId('content_id')->nullable()->constrained('contents')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

