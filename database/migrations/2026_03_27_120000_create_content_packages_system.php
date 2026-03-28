<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('cover_video')->nullable();
            $table->string('cover_video_youtube_id', 32)->nullable();
            $table->boolean('cover_video_is_unlisted')->default(false);
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamp('sale_start_at')->nullable();
            $table->timestamp('sale_end_at')->nullable();
            $table->boolean('is_sale_enabled')->default(true);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('marketing_headline')->nullable();
            $table->json('marketing_highlights')->nullable();
            $table->json('marketing_benefits')->nullable();
            $table->string('cta_label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('content_package_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_package_id')->constrained('content_packages')->cascadeOnDelete();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['content_package_id', 'content_id']);
        });

        Schema::create('cart_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_package_id')->constrained('content_packages')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'content_package_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('content_package_id')
                ->nullable()
                ->after('content_id')
                ->constrained('content_packages')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['content_package_id']);
            $table->dropColumn('content_package_id');
        });

        Schema::dropIfExists('cart_packages');
        Schema::dropIfExists('content_package_content');
        Schema::dropIfExists('content_packages');
    }
};
