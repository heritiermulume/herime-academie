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
        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('subtitle')->nullable();
                $table->string('image')->nullable();
                $table->string('mobile_image')->nullable();
                $table->string('button1_text')->nullable();
                $table->string('button1_url')->nullable();
                $table->string('button1_style')->nullable();
                $table->string('button1_target')->nullable();
                $table->string('button2_text')->nullable();
                $table->string('button2_url')->nullable();
                $table->string('button2_style')->nullable();
                $table->string('button2_target')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};


