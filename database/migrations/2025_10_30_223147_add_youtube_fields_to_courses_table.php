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
        Schema::table('courses', function (Blueprint $table) {
            $table->string('video_preview_youtube_id')->nullable()->after('video_preview');
            $table->boolean('video_preview_is_unlisted')->default(false)->after('video_preview_youtube_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['video_preview_youtube_id', 'video_preview_is_unlisted']);
        });
    }
};
