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
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->string('youtube_video_id')->nullable()->after('content_url');
            $table->boolean('is_unlisted')->default(false)->after('youtube_video_id');
            $table->text('youtube_embed_url')->nullable()->after('is_unlisted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropColumn(['youtube_video_id', 'is_unlisted', 'youtube_embed_url']);
        });
    }
};
