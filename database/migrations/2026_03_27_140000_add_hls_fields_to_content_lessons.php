<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_lessons', function (Blueprint $table) {
            $table->string('hls_manifest_path')->nullable()->after('file_path');
            $table->string('hls_status', 32)->nullable()->after('hls_manifest_path');
        });
    }

    public function down(): void
    {
        Schema::table('content_lessons', function (Blueprint $table) {
            $table->dropColumn(['hls_manifest_path', 'hls_status']);
        });
    }
};
