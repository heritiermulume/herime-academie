<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cette migration s'assure que les colonnes SSO existent dans la table users.
     * Elle est idempotente et n'échouera pas si les colonnes existent déjà.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        // Vérifier et ajouter sso_id si elle n'existe pas
        if (!Schema::hasColumn('users', 'sso_id')) {
            try {
                // Déterminer la colonne après laquelle ajouter
                $afterColumn = 'preferences';
                if (!Schema::hasColumn('users', 'preferences')) {
                    $afterColumn = 'last_login_at';
                    if (!Schema::hasColumn('users', 'last_login_at')) {
                        $afterColumn = 'is_active';
                    }
                }
                
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE `users` ADD COLUMN `sso_id` VARCHAR(255) NULL AFTER `{$afterColumn}`");
                    // Ajouter l'index unique après avoir créé la colonne
                    DB::statement('ALTER TABLE `users` ADD UNIQUE INDEX `users_sso_id_unique` (`sso_id`)');
                } else {
                    Schema::table('users', function (Blueprint $table) use ($afterColumn) {
                        $table->string('sso_id')->nullable()->unique()->after($afterColumn);
                    });
                }
            } catch (\Exception $e) {
                // Si la colonne existe déjà ou s'il y a une erreur, continuer
                Log::info('Migration SSO: sso_id might already exist', ['error' => $e->getMessage()]);
            }
        } else {
            // Si la colonne existe mais n'a pas d'index unique, l'ajouter
            try {
                if ($driver === 'mysql') {
                    $indexes = DB::select("SHOW INDEXES FROM users WHERE Column_name = 'sso_id' AND Non_unique = 0");
                    if (empty($indexes)) {
                        DB::statement('ALTER TABLE `users` ADD UNIQUE INDEX `users_sso_id_unique` (`sso_id`)');
                    }
                }
            } catch (\Exception $e) {
                // Ignorer si l'index existe déjà ou s'il y a une erreur
            }
        }

        // Vérifier et ajouter sso_provider si elle n'existe pas
        if (!Schema::hasColumn('users', 'sso_provider')) {
            try {
                $afterColumn = Schema::hasColumn('users', 'sso_id') ? 'sso_id' : 'preferences';
                if (!Schema::hasColumn('users', $afterColumn)) {
                    $afterColumn = 'last_login_at';
                }
                
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE `users` ADD COLUMN `sso_provider` VARCHAR(100) NOT NULL DEFAULT 'herime' AFTER `{$afterColumn}`");
                } else {
                    Schema::table('users', function (Blueprint $table) use ($afterColumn) {
                        $table->string('sso_provider', 100)->default('herime')->after($afterColumn);
                    });
                }
            } catch (\Exception $e) {
                Log::info('Migration SSO: sso_provider might already exist', ['error' => $e->getMessage()]);
            }
        }

        // Vérifier et ajouter sso_metadata si elle n'existe pas
        if (!Schema::hasColumn('users', 'sso_metadata')) {
            try {
                $afterColumn = Schema::hasColumn('users', 'sso_provider') ? 'sso_provider' : 'sso_id';
                if (!Schema::hasColumn('users', $afterColumn)) {
                    $afterColumn = 'preferences';
                }
                
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE `users` ADD COLUMN `sso_metadata` JSON NULL AFTER `{$afterColumn}`");
                } else {
                    Schema::table('users', function (Blueprint $table) use ($afterColumn) {
                        $table->json('sso_metadata')->nullable()->after($afterColumn);
                    });
                }
            } catch (\Exception $e) {
                Log::info('Migration SSO: sso_metadata might already exist', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ne pas supprimer les colonnes SSO car elles peuvent être utilisées ailleurs
        // Cette migration est uniquement pour s'assurer qu'elles existent
    }
};
