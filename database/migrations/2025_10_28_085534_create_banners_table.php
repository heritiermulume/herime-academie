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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('image');
            $table->string('mobile_image')->nullable(); // Image optimisée pour mobile 16:9
            $table->string('button1_text')->nullable();
            $table->string('button1_url')->nullable();
            $table->string('button1_style')->default('primary'); // primary, secondary, warning, etc.
            $table->string('button2_text')->nullable();
            $table->string('button2_url')->nullable();
            $table->string('button2_style')->default('secondary');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
