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
        // Cette migration sera exécutée avant le renommage, donc on utilise 'courses'
        // Le renommage en 'show_customers_count' sera fait dans la migration de renommage
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
