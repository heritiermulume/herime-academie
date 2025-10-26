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
            // Supprimer les champs statiques qui sont maintenant calculés dynamiquement
            $table->dropColumn([
                'duration',        // Calculé à partir des leçons
                'lessons_count',   // Calculé à partir des sections/leçons
                'students_count',  // Calculé à partir des enrollments
                'rating',          // Calculé à partir des reviews
                'reviews_count'    // Calculé à partir des reviews
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Restaurer les champs statiques si nécessaire
            $table->integer('duration')->default(0)->after('language');
            $table->integer('lessons_count')->default(0)->after('duration');
            $table->integer('students_count')->default(0)->after('lessons_count');
            $table->decimal('rating', 3, 2)->default(0)->after('students_count');
            $table->integer('reviews_count')->default(0)->after('rating');
        });
    }
};