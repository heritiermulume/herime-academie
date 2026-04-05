<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('video_preview_hls_manifest_path')->nullable();
            $table->string('video_preview_hls_status', 32)->nullable();
        });

        Schema::table('content_packages', function (Blueprint $table) {
            $table->string('cover_video_hls_manifest_path')->nullable();
            $table->string('cover_video_hls_status', 32)->nullable();
        });

        // Ancien schéma HLS des leçons (dossier hls/ partagé) : forcer un réencodage avec le nouveau schéma {stem}_hls/
        DB::table('content_lessons')->whereNotNull('hls_manifest_path')->update([
            'hls_manifest_path' => null,
            'hls_status' => null,
        ]);
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['video_preview_hls_manifest_path', 'video_preview_hls_status']);
        });

        Schema::table('content_packages', function (Blueprint $table) {
            $table->dropColumn(['cover_video_hls_manifest_path', 'cover_video_hls_status']);
        });
    }
};
