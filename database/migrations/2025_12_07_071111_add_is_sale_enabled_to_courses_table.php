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
        $tableName = Schema::hasTable('contents') ? 'contents' : (Schema::hasTable('courses') ? 'courses' : null);
        if (!$tableName) {
            return;
        }
        if (Schema::hasColumn($tableName, 'is_sale_enabled')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->boolean('is_sale_enabled')->default(true)->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Schema::hasTable('contents') ? 'contents' : (Schema::hasTable('courses') ? 'courses' : null);
        if (!$tableName) {
            return;
        }
        if (!Schema::hasColumn($tableName, 'is_sale_enabled')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('is_sale_enabled');
        });
    }
};
