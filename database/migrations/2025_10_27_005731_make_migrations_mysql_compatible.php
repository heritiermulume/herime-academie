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
        // Ajouter la colonne content si elle n'existe pas (pour compatibilité MySQL)
        Schema::table('course_lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('course_lessons', 'content')) {
                $table->text('content')->nullable()->after('content_text');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_lessons', function (Blueprint $table) {
            if (Schema::hasColumn('course_lessons', 'content')) {
                $table->dropColumn('content');
            }
        });
    }
};
