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
        // Compatible rename: si la table a déjà été renommée, on ajoute directement show_customers_count.
        $tableName = Schema::hasTable('contents') ? 'contents' : (Schema::hasTable('courses') ? 'courses' : null);
        if (!$tableName) {
            return;
        }

        if ($tableName === 'contents') {
            if (Schema::hasColumn('contents', 'show_customers_count')) {
                return;
            }
            Schema::table('contents', function (Blueprint $table) {
                $table->boolean('show_customers_count')->default(false)->after('is_featured');
            });
            return;
        }

        if (Schema::hasColumn('courses', 'show_students_count')) {
            return;
        }
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('show_students_count')->default(false)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Si la table a été renommée, utiliser 'contents', sinon 'courses'
        $tableName = Schema::hasTable('contents') ? 'contents' : 'courses';
        $columnName = Schema::hasColumn('contents', 'show_customers_count') ? 'show_customers_count' : 'show_students_count';
        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
            $table->dropColumn($columnName);
        });
    }
};
