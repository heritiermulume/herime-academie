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
     * Cette migration renomme :
     * - Table courses → contents
     * - Table course_sections → content_sections
     * - Table course_lessons → content_lessons
     * - Table instructor_payouts → provider_payouts
     * - Colonne instructor_id → provider_id
     * - Colonne customers_count → customers_count
     * - Colonne course_id → content_id dans toutes les tables
     * - Colonne is_external_instructor → is_external_provider
     */
    public function up(): void
    {
        // SQLite (tests) ne supporte pas les statements MySQL "ALTER TABLE ... CHANGE" ni information_schema.
        // On exécute une version compatible SQLite (rename uniquement) et on ignore la gestion avancée des FK.
        if (DB::getDriverName() === 'sqlite') {
            $this->sqliteUp();
            return;
        }

        // Étape 1: Supprimer toutes les contraintes de clés étrangères qui référencent les tables à renommer
        $this->dropForeignKeys();

        // Étape 2: Renommer les colonnes dans la table users
        if (Schema::hasColumn('users', 'is_external_instructor')) {
            DB::statement('ALTER TABLE `users` CHANGE `is_external_instructor` `is_external_provider` TINYINT(1) NULL DEFAULT NULL');
        }

        // Étape 3: Renommer les colonnes dans la table courses avant de renommer la table
        if (Schema::hasTable('courses')) {
            if (Schema::hasColumn('courses', 'instructor_id')) {
                DB::statement('ALTER TABLE `courses` CHANGE `instructor_id` `provider_id` BIGINT UNSIGNED NOT NULL');
            }
            if (Schema::hasColumn('courses', 'students_count')) {
                DB::statement('ALTER TABLE `courses` CHANGE `students_count` `customers_count` INT NOT NULL DEFAULT 0');
            }
            if (Schema::hasColumn('courses', 'show_students_count')) {
                DB::statement('ALTER TABLE `courses` CHANGE `show_students_count` `show_customers_count` TINYINT(1) NOT NULL DEFAULT 0');
            }
        }

        // Étape 4: Renommer les colonnes course_id en content_id dans course_sections et course_lessons AVANT de renommer les tables
        if (Schema::hasTable('course_sections') && Schema::hasColumn('course_sections', 'course_id')) {
            DB::statement('ALTER TABLE `course_sections` CHANGE `course_id` `content_id` BIGINT UNSIGNED NOT NULL');
        }
        if (Schema::hasTable('course_lessons') && Schema::hasColumn('course_lessons', 'course_id')) {
            DB::statement('ALTER TABLE `course_lessons` CHANGE `course_id` `content_id` BIGINT UNSIGNED NOT NULL');
        }

        // Étape 5: Renommer la table courses en contents
        if (Schema::hasTable('courses')) {
            Schema::rename('courses', 'contents');
        }

        // Étape 6: Renommer la table course_sections en content_sections
        if (Schema::hasTable('course_sections')) {
            Schema::rename('course_sections', 'content_sections');
        }

        // Étape 7: Renommer la table course_lessons en content_lessons
        if (Schema::hasTable('course_lessons')) {
            Schema::rename('course_lessons', 'content_lessons');
        }

        // Étape 8: Renommer les colonnes course_id en content_id dans toutes les autres tables
        $tablesWithCourseId = [
            'enrollments',
            'order_items',
            'reviews',
            'certificates',
            'cart_items',
            'lesson_progress',
            'lesson_notes',
            'lesson_resources',
            'lesson_discussions',
            'course_downloads',
            'messages',
            'video_access_tokens',
        ];

        foreach ($tablesWithCourseId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'course_id')) {
                DB::statement("ALTER TABLE `{$tableName}` CHANGE `course_id` `content_id` BIGINT UNSIGNED NOT NULL");
            }
        }

        // Étape 9: Renommer la table instructor_payouts et ses colonnes
        if (Schema::hasTable('instructor_payouts')) {
            // Renommer les colonnes avant de renommer la table
            if (Schema::hasColumn('instructor_payouts', 'instructor_id')) {
                DB::statement('ALTER TABLE `instructor_payouts` CHANGE `instructor_id` `provider_id` BIGINT UNSIGNED NOT NULL');
            }
            if (Schema::hasColumn('instructor_payouts', 'course_id')) {
                DB::statement('ALTER TABLE `instructor_payouts` CHANGE `course_id` `content_id` BIGINT UNSIGNED NOT NULL');
            }
            
            // Renommer la table
            Schema::rename('instructor_payouts', 'provider_payouts');
        }

        // Étape 10: Recréer toutes les contraintes de clés étrangères avec les nouveaux noms
        $this->recreateForeignKeys();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->sqliteDown();
            return;
        }

        // Supprimer les contraintes de clés étrangères
        $this->dropForeignKeys();

        // Renommer les tables en arrière
        if (Schema::hasTable('provider_payouts')) {
            if (Schema::hasColumn('provider_payouts', 'provider_id')) {
                DB::statement('ALTER TABLE `provider_payouts` CHANGE `provider_id` `instructor_id` BIGINT UNSIGNED NOT NULL');
            }
            if (Schema::hasColumn('provider_payouts', 'content_id')) {
                DB::statement('ALTER TABLE `provider_payouts` CHANGE `content_id` `course_id` BIGINT UNSIGNED NOT NULL');
            }
            Schema::rename('provider_payouts', 'instructor_payouts');
        }

        // Renommer content_id en course_id
        $tablesWithContentId = [
            'enrollments',
            'content_sections',
            'content_lessons',
            'order_items',
            'reviews',
            'certificates',
            'cart_items',
            'lesson_progress',
            'lesson_notes',
            'lesson_resources',
            'lesson_discussions',
            'course_downloads',
            'messages',
            'video_access_tokens',
        ];

        foreach ($tablesWithContentId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'content_id')) {
                DB::statement("ALTER TABLE `{$tableName}` CHANGE `content_id` `course_id` BIGINT UNSIGNED NOT NULL");
            }
        }

        // Renommer les tables
        if (Schema::hasTable('content_lessons')) {
            Schema::rename('content_lessons', 'course_lessons');
        }
        if (Schema::hasTable('content_sections')) {
            Schema::rename('content_sections', 'course_sections');
        }
        if (Schema::hasTable('contents')) {
            if (Schema::hasColumn('contents', 'provider_id')) {
                DB::statement('ALTER TABLE `contents` CHANGE `provider_id` `instructor_id` BIGINT UNSIGNED NOT NULL');
            }
            if (Schema::hasColumn('contents', 'customers_count')) {
                DB::statement('ALTER TABLE `contents` CHANGE `customers_count` `customers_count` INT NOT NULL DEFAULT 0');
            }
            Schema::rename('contents', 'courses');
        }

        // Renommer la colonne dans users
        if (Schema::hasColumn('users', 'is_external_provider')) {
            DB::statement('ALTER TABLE `users` CHANGE `is_external_provider` `is_external_instructor` TINYINT(1) NULL DEFAULT NULL');
        }

        // Renommer show_customers_count en show_students_count
        if (Schema::hasTable('contents') && Schema::hasColumn('contents', 'show_customers_count')) {
            DB::statement('ALTER TABLE `contents` CHANGE `show_customers_count` `show_students_count` TINYINT(1) NOT NULL DEFAULT 0');
        } elseif (Schema::hasTable('courses') && Schema::hasColumn('courses', 'show_customers_count')) {
            DB::statement('ALTER TABLE `courses` CHANGE `show_customers_count` `show_students_count` TINYINT(1) NOT NULL DEFAULT 0');
        }

        // Recréer les contraintes de clés étrangères
        $this->recreateForeignKeysOldNames();
    }

    /**
     * Supprimer toutes les contraintes de clés étrangères
     */
    private function dropForeignKeys(): void
    {
        $foreignKeys = [
            ['table' => 'lesson_progress', 'columns' => ['user_id', 'content_id', 'lesson_id']],
            ['table' => 'cart_items', 'columns' => ['user_id', 'content_id']],
            ['table' => 'messages', 'columns' => ['sender_id', 'receiver_id', 'content_id']],
            ['table' => 'reviews', 'columns' => ['user_id', 'content_id']],
            ['table' => 'certificates', 'columns' => ['user_id', 'content_id']],
            ['table' => 'enrollments', 'columns' => ['user_id', 'content_id']],
            ['table' => 'course_lessons', 'columns' => ['content_id', 'section_id']],
            ['table' => 'content_lessons', 'columns' => ['content_id', 'section_id']],
            ['table' => 'course_sections', 'columns' => ['content_id']],
            ['table' => 'content_sections', 'columns' => ['content_id']],
            ['table' => 'courses', 'columns' => ['instructor_id', 'category_id']],
            ['table' => 'contents', 'columns' => ['provider_id', 'category_id']],
            ['table' => 'instructor_payouts', 'columns' => ['instructor_id', 'order_id', 'content_id']],
            ['table' => 'provider_payouts', 'columns' => ['provider_id', 'order_id', 'content_id']],
            ['table' => 'order_items', 'columns' => ['order_id', 'content_id']],
            ['table' => 'order_items', 'columns' => ['order_id', 'content_id']],
        ];

        foreach ($foreignKeys as $fk) {
            if (Schema::hasTable($fk['table'])) {
                try {
                    Schema::table($fk['table'], function (Blueprint $table) use ($fk) {
                        foreach ($fk['columns'] as $column) {
                            $constraintName = $this->getForeignKeyName($fk['table'], $column);
                            try {
                                $table->dropForeign($constraintName);
                            } catch (\Exception $e) {
                                // Ignorer si la contrainte n'existe pas
                            }
                        }
                    });
                } catch (\Exception $e) {
                    // Ignorer si la table n'existe pas
                }
            }
        }
    }

    /**
     * Vérifier si une contrainte de clé étrangère existe
     */
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        
        $result = DB::select(
            "SELECT COUNT(*) as count 
             FROM information_schema.TABLE_CONSTRAINTS 
             WHERE CONSTRAINT_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = ? 
             AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$database, $table, $constraintName]
        );
        
        return $result[0]->count > 0;
    }

    /**
     * Recréer toutes les contraintes de clés étrangères avec les nouveaux noms
     */
    private function recreateForeignKeys(): void
    {
        // Content sections foreign keys
        if (Schema::hasTable('content_sections') && Schema::hasColumn('content_sections', 'content_id')) {
            $constraintName = 'content_sections_content_id_foreign';
            if (!$this->foreignKeyExists('content_sections', $constraintName)) {
                Schema::table('content_sections', function (Blueprint $table) {
                    $table->foreign('content_id')->references('id')->on('contents')->onDelete('cascade');
                });
            }
        }

        // Content lessons foreign keys
        if (Schema::hasTable('content_lessons') && Schema::hasColumn('content_lessons', 'content_id')) {
            $constraintName = 'content_lessons_content_id_foreign';
            if (!$this->foreignKeyExists('content_lessons', $constraintName)) {
                Schema::table('content_lessons', function (Blueprint $table) {
                    $table->foreign('content_id')->references('id')->on('contents')->onDelete('cascade');
                });
            }
            if (Schema::hasColumn('content_lessons', 'section_id')) {
                $constraintName = 'content_lessons_section_id_foreign';
                if (!$this->foreignKeyExists('content_lessons', $constraintName)) {
                    Schema::table('content_lessons', function (Blueprint $table) {
                        $table->foreign('section_id')->references('id')->on('content_sections')->onDelete('cascade');
                    });
                }
            }
        }

        // Helper pour ajouter une contrainte seulement si elle n'existe pas
        $addForeignKeyIfNotExists = function($table, $column, $referencedTable, $referencedColumn = 'id', $constraintName = null) {
            if (!$constraintName) {
                $constraintName = "{$table}_{$column}_foreign";
            }
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column) && !$this->foreignKeyExists($table, $constraintName)) {
                Schema::table($table, function (Blueprint $blueprint) use ($column, $referencedTable, $referencedColumn) {
                    $blueprint->foreign($column)->references($referencedColumn)->on($referencedTable)->onDelete('cascade');
                });
            }
        };

        // Contents foreign keys
        $addForeignKeyIfNotExists('contents', 'provider_id', 'users');
        $addForeignKeyIfNotExists('contents', 'category_id', 'categories');

        // Enrollments foreign keys
        $addForeignKeyIfNotExists('enrollments', 'user_id', 'users');
        $addForeignKeyIfNotExists('enrollments', 'content_id', 'contents');

        // Certificates foreign keys
        $addForeignKeyIfNotExists('certificates', 'user_id', 'users');
        $addForeignKeyIfNotExists('certificates', 'content_id', 'contents');

        // Reviews foreign keys
        $addForeignKeyIfNotExists('reviews', 'user_id', 'users');
        $addForeignKeyIfNotExists('reviews', 'content_id', 'contents');

        // Messages foreign keys
        $addForeignKeyIfNotExists('messages', 'sender_id', 'users');
        $addForeignKeyIfNotExists('messages', 'receiver_id', 'users');
        $addForeignKeyIfNotExists('messages', 'content_id', 'contents');

        // Cart items foreign keys
        $addForeignKeyIfNotExists('cart_items', 'user_id', 'users');
        $addForeignKeyIfNotExists('cart_items', 'content_id', 'contents');

        // Lesson progress foreign keys
        $addForeignKeyIfNotExists('lesson_progress', 'user_id', 'users');
        $addForeignKeyIfNotExists('lesson_progress', 'content_id', 'contents');
        $addForeignKeyIfNotExists('lesson_progress', 'lesson_id', 'content_lessons');

        // Order items foreign keys
        $addForeignKeyIfNotExists('order_items', 'order_id', 'orders');
        $addForeignKeyIfNotExists('order_items', 'content_id', 'contents');

        // Provider payouts foreign keys
        $addForeignKeyIfNotExists('provider_payouts', 'provider_id', 'users');
        $addForeignKeyIfNotExists('provider_payouts', 'order_id', 'orders');
        $addForeignKeyIfNotExists('provider_payouts', 'content_id', 'contents');

        // Course downloads foreign keys
        $addForeignKeyIfNotExists('course_downloads', 'user_id', 'users');
        $addForeignKeyIfNotExists('course_downloads', 'content_id', 'contents');

        // Lesson notes foreign keys
        $addForeignKeyIfNotExists('lesson_notes', 'user_id', 'users');
        $addForeignKeyIfNotExists('lesson_notes', 'content_id', 'contents');
        $addForeignKeyIfNotExists('lesson_notes', 'lesson_id', 'content_lessons');

        // Lesson resources foreign keys
        $addForeignKeyIfNotExists('lesson_resources', 'lesson_id', 'content_lessons');

        // Lesson discussions foreign keys
        $addForeignKeyIfNotExists('lesson_discussions', 'user_id', 'users');
        $addForeignKeyIfNotExists('lesson_discussions', 'content_id', 'contents');
        $addForeignKeyIfNotExists('lesson_discussions', 'lesson_id', 'content_lessons');
    }

    /**
     * Recréer les contraintes avec les anciens noms (pour rollback)
     */
    private function recreateForeignKeysOldNames(): void
    {
        // Course sections foreign keys
        if (Schema::hasTable('course_sections')) {
            Schema::table('course_sections', function (Blueprint $table) {
                $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
            });
        }

        // Course lessons foreign keys
        if (Schema::hasTable('course_lessons')) {
            Schema::table('course_lessons', function (Blueprint $table) {
                $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
                $table->foreign('section_id')->references('id')->on('course_sections')->onDelete('cascade');
            });
        }

        // Courses foreign keys
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            });
        }

        // Enrollments foreign keys
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                if (Schema::hasColumn('enrollments', 'content_id')) {
                    $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
                }
            });
        }

        // Certificates foreign keys
        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                if (Schema::hasColumn('certificates', 'content_id')) {
                    $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
                }
            });
        }

        // Reviews foreign keys
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                if (Schema::hasColumn('reviews', 'content_id')) {
                    $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
                }
            });
        }

        // Messages foreign keys
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
                if (Schema::hasColumn('messages', 'content_id')) {
                    $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
                }
            });
        }

        // Cart items foreign keys
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                if (Schema::hasColumn('cart_items', 'content_id')) {
                    $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
                }
            });
        }

        // Lesson progress foreign keys
        if (Schema::hasTable('lesson_progress')) {
            Schema::table('lesson_progress', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                if (Schema::hasColumn('lesson_progress', 'content_id')) {
                    $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
                }
                if (Schema::hasColumn('lesson_progress', 'lesson_id')) {
                    $table->foreign('lesson_id')->references('id')->on('course_lessons')->onDelete('cascade');
                }
            });
        }

        // Order items foreign keys
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                if (Schema::hasColumn('order_items', 'content_id')) {
                    $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
                }
            });
        }

        // Instructor payouts foreign keys
        if (Schema::hasTable('instructor_payouts')) {
            Schema::table('instructor_payouts', function (Blueprint $table) {
                $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                if (Schema::hasColumn('instructor_payouts', 'content_id')) {
                    $table->foreign('content_id')->references('id')->on('courses')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Obtenir le nom de la contrainte de clé étrangère
     */
    private function getForeignKeyName(string $table, string $column): string
    {
        // Laravel génère généralement les noms de contraintes comme : {table}_{column}_foreign
        return "{$table}_{$column}_foreign";
    }

    /**
     * SQLite-friendly migration path (used in tests).
     */
    private function sqliteUp(): void
    {
        // users: is_external_instructor -> is_external_provider
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_external_instructor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('is_external_instructor', 'is_external_provider');
            });
        }

        // courses column renames before table rename
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (Schema::hasColumn('courses', 'instructor_id')) {
                    $table->renameColumn('instructor_id', 'provider_id');
                }
                if (Schema::hasColumn('courses', 'students_count')) {
                    $table->renameColumn('students_count', 'customers_count');
                }
                if (Schema::hasColumn('courses', 'show_students_count')) {
                    $table->renameColumn('show_students_count', 'show_customers_count');
                }
            });
        }

        // course_sections / course_lessons course_id -> content_id
        if (Schema::hasTable('course_sections') && Schema::hasColumn('course_sections', 'course_id')) {
            Schema::table('course_sections', function (Blueprint $table) {
                $table->renameColumn('course_id', 'content_id');
            });
        }
        if (Schema::hasTable('course_lessons') && Schema::hasColumn('course_lessons', 'course_id')) {
            Schema::table('course_lessons', function (Blueprint $table) {
                $table->renameColumn('course_id', 'content_id');
            });
        }

        // rename tables
        if (Schema::hasTable('courses')) {
            Schema::rename('courses', 'contents');
        }
        if (Schema::hasTable('course_sections')) {
            Schema::rename('course_sections', 'content_sections');
        }
        if (Schema::hasTable('course_lessons')) {
            Schema::rename('course_lessons', 'content_lessons');
        }

        // other tables: course_id -> content_id
        $tablesWithCourseId = [
            'enrollments',
            'order_items',
            'reviews',
            'certificates',
            'cart_items',
            'lesson_progress',
            'lesson_notes',
            'lesson_resources',
            'lesson_discussions',
            'course_downloads',
            'messages',
            'video_access_tokens',
        ];
        foreach ($tablesWithCourseId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'course_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('course_id', 'content_id');
                });
            }
        }

        // instructor_payouts -> provider_payouts (+ column renames)
        if (Schema::hasTable('instructor_payouts')) {
            Schema::table('instructor_payouts', function (Blueprint $table) {
                if (Schema::hasColumn('instructor_payouts', 'instructor_id')) {
                    $table->renameColumn('instructor_id', 'provider_id');
                }
                if (Schema::hasColumn('instructor_payouts', 'course_id')) {
                    $table->renameColumn('course_id', 'content_id');
                }
            });
            Schema::rename('instructor_payouts', 'provider_payouts');
        }
    }

    private function sqliteDown(): void
    {
        // provider_payouts -> instructor_payouts (+ column renames)
        if (Schema::hasTable('provider_payouts')) {
            Schema::table('provider_payouts', function (Blueprint $table) {
                if (Schema::hasColumn('provider_payouts', 'provider_id')) {
                    $table->renameColumn('provider_id', 'instructor_id');
                }
                if (Schema::hasColumn('provider_payouts', 'content_id')) {
                    $table->renameColumn('content_id', 'course_id');
                }
            });
            Schema::rename('provider_payouts', 'instructor_payouts');
        }

        // content_id -> course_id in other tables
        $tablesWithContentId = [
            'enrollments',
            'content_sections',
            'content_lessons',
            'order_items',
            'reviews',
            'certificates',
            'cart_items',
            'lesson_progress',
            'lesson_notes',
            'lesson_resources',
            'lesson_discussions',
            'course_downloads',
            'messages',
            'video_access_tokens',
        ];
        foreach ($tablesWithContentId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'content_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('content_id', 'course_id');
                });
            }
        }

        // tables back
        if (Schema::hasTable('content_lessons')) {
            Schema::rename('content_lessons', 'course_lessons');
        }
        if (Schema::hasTable('content_sections')) {
            Schema::rename('content_sections', 'course_sections');
        }
        if (Schema::hasTable('contents')) {
            Schema::table('contents', function (Blueprint $table) {
                if (Schema::hasColumn('contents', 'provider_id')) {
                    $table->renameColumn('provider_id', 'instructor_id');
                }
                if (Schema::hasColumn('contents', 'customers_count')) {
                    $table->renameColumn('customers_count', 'students_count');
                }
                if (Schema::hasColumn('contents', 'show_customers_count')) {
                    $table->renameColumn('show_customers_count', 'show_students_count');
                }
            });
            Schema::rename('contents', 'courses');
        }

        // users column back
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_external_provider')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('is_external_provider', 'is_external_instructor');
            });
        }
    }
};
