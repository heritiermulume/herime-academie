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
        if (Schema::hasColumn($tableName, 'sale_start_at')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->timestamp('sale_start_at')->nullable()->after('sale_price');
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
        if (!Schema::hasColumn($tableName, 'sale_start_at')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('sale_start_at');
        });
    }
};

