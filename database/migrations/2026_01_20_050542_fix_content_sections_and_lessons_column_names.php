<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cette migration corrige le problème où les colonnes course_id n'ont pas été
     * renommées en content_id dans les tables content_sections et content_lessons
     * lors de la migration précédente.
     */
    public function up(): void
    {
        // Renommer course_id en content_id dans content_sections
        if (Schema::hasTable('content_sections') && Schema::hasColumn('content_sections', 'course_id')) {
            // Supprimer d'abord les contraintes de clé étrangère existantes
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'content_sections' 
                AND COLUMN_NAME = 'course_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE `content_sections` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }
            
            // Renommer la colonne
            DB::statement('ALTER TABLE `content_sections` CHANGE `course_id` `content_id` BIGINT UNSIGNED NOT NULL');
            
            // Recréer la contrainte de clé étrangère
            if (Schema::hasTable('contents')) {
                DB::statement('ALTER TABLE `content_sections` ADD CONSTRAINT `content_sections_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE');
            }
        }

        // Renommer course_id en content_id dans content_lessons
        if (Schema::hasTable('content_lessons') && Schema::hasColumn('content_lessons', 'course_id')) {
            // Supprimer d'abord les contraintes de clé étrangère existantes
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'content_lessons' 
                AND COLUMN_NAME = 'course_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE `content_lessons` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }
            
            // Renommer la colonne
            DB::statement('ALTER TABLE `content_lessons` CHANGE `course_id` `content_id` BIGINT UNSIGNED NOT NULL');
            
            // Recréer la contrainte de clé étrangère
            if (Schema::hasTable('contents')) {
                DB::statement('ALTER TABLE `content_lessons` ADD CONSTRAINT `content_lessons_content_id_foreign` FOREIGN KEY (`content_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Renommer content_id en course_id dans content_sections
        if (Schema::hasTable('content_sections') && Schema::hasColumn('content_sections', 'content_id')) {
            // Supprimer les contraintes de clé étrangère
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'content_sections' 
                AND COLUMN_NAME = 'content_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE `content_sections` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }
            
            // Renommer la colonne
            DB::statement('ALTER TABLE `content_sections` CHANGE `content_id` `course_id` BIGINT UNSIGNED NOT NULL');
            
            // Recréer la contrainte de clé étrangère avec l'ancien nom
            if (Schema::hasTable('contents')) {
                DB::statement('ALTER TABLE `content_sections` ADD CONSTRAINT `content_sections_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE');
            }
        }

        // Renommer content_id en course_id dans content_lessons
        if (Schema::hasTable('content_lessons') && Schema::hasColumn('content_lessons', 'content_id')) {
            // Supprimer les contraintes de clé étrangère
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'content_lessons' 
                AND COLUMN_NAME = 'content_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE `content_lessons` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }
            
            // Renommer la colonne
            DB::statement('ALTER TABLE `content_lessons` CHANGE `content_id` `course_id` BIGINT UNSIGNED NOT NULL');
            
            // Recréer la contrainte de clé étrangère avec l'ancien nom
            if (Schema::hasTable('contents')) {
                DB::statement('ALTER TABLE `content_lessons` ADD CONSTRAINT `content_lessons_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `contents` (`id`) ON DELETE CASCADE');
            }
        }
    }
};
